<?php

namespace App\Services\Owners;

use App\Models\Booking;
use App\Models\BookingPayment;
use App\Models\Property;
use App\Models\PropertyPayment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OwnerStatementService
{
    public const OWNER_BLOCK_PLATFORM_ID = 7;

    /**
     * @return list<array{id: int, property_title: string|null}>
     */
    public function propertiesFor(User $user): array
    {
        return $this->propertyQueryFor($user)
            ->orderBy('property_title')
            ->get(['id', 'property_title'])
            ->map(fn (Property $property): array => [
                'id' => $property->id,
                'property_title' => $property->property_title,
            ])
            ->values()
            ->all();
    }

    /**
     * @param  array{property_id: int, date_range: string, month?: int|string|null, year?: int|string|null}  $filters
     * @return array<string, mixed>
     */
    public function report(User $user, array $filters): array
    {
        $property = $this->resolveProperty($user, (int) $filters['property_id']);
        $range = $this->resolveDateRange(
            (string) $filters['date_range'],
            $filters['month'] ?? null,
            $filters['year'] ?? null,
        );

        $bookings = Booking::query()
            ->notDeleted()
            ->where('property_id', $property->id)
            ->where(fn ($query) => $query->where('cancelled_booking', false)->orWhereNull('cancelled_booking'))
            ->where(function ($query) use ($range): void {
                if ($range['use_reservation_dates']) {
                    $query->whereBetween('reservation_start_date', [$range['start_date'], $range['end_date']]);

                    return;
                }

                if ($range['year'] !== null) {
                    $query->where('year', (string) $range['year']);
                }

                if ($range['months'] !== []) {
                    $query->whereIn('month', $range['months']);
                }
            })
            ->with('guests')
            ->get();

        $totals = $this->emptyTotals($property, $range);

        foreach ($bookings as $booking) {
            $totals['total_reservations']++;
            $totals['accomodations'] += (float) ($booking->accomodations ?? 0);
            $totals['cleaning_fee'] += (float) ($booking->cleaning_fee ?? 0);
            $totals['tot_charged_to_guest'] += (float) ($booking->tot_charged_to_guest ?? 0);
            $totals['total_guest_paid'] += (float) ($booking->total_guest_paid ?? 0);
            $totals['management_fee'] += (float) ($booking->management_fee ?? 0);
            $totals['owner_payout'] += (float) ($booking->owner_payout_amount_from_airconcierge ?? 0);

            $nights = (int) ($booking->no_of_nights ?? 0);
            if ((int) $booking->platform_id === self::OWNER_BLOCK_PLATFORM_ID) {
                $totals['blocked_nights'] += $nights;
            } else {
                $totals['booked_nights'] += $nights;
            }

            $guest = $booking->guests->first();
            $guestName = $guest !== null ? (string) ($guest->guest_name ?: 'Guest') : 'Guest';

            if (! empty($booking->owner_notes)) {
                $totals['owner_notes'][] = [
                    'guest' => $guestName,
                    'start' => optional($booking->reservation_start_date)?->toDateString(),
                    'end' => optional($booking->reservation_end_date)?->toDateString(),
                    'comment' => $booking->owner_notes,
                ];
            }

            $totals['booking_payments'] += (float) BookingPayment::query()
                ->where('booking_id', $booking->id)
                ->where(fn ($query) => $query->where('deleted', false)->orWhereNull('deleted'))
                ->sum('amount');
        }

        $propertyPayments = PropertyPayment::query()
            ->where('property_id', $property->id)
            ->where(fn ($query) => $query->where('deleted', false)->orWhereNull('deleted'))
            ->whereBetween('payment_date', [$range['start_date'], $range['end_date']])
            ->sum('amount');

        $totals['property_payments'] = (float) $propertyPayments;
        $totals['total_income'] = $totals['accomodations']
            + $totals['cleaning_fee']
            + $totals['tot_charged_to_guest'];
        $totals['total_expenses'] = $totals['management_fee'];
        $totals['net_income'] = $totals['owner_payout'];

        $availableNights = max(1, (int) Carbon::parse($range['start_date'])->diffInDays(Carbon::parse($range['end_date'])) + 1);
        $totals['occupancy_percentage'] = round(($totals['booked_nights'] / $availableNights) * 100, 2);
        $totals['average_daily_rate'] = $totals['booked_nights'] > 0
            ? round($totals['accomodations'] / $totals['booked_nights'], 2)
            : 0.0;

        $totals['schema_gaps'] = [
            'daily_utility_fee',
            'security_deposit',
            'pet_fee',
            'site_listing_fee',
            'credit_card_transaction_fee',
            'payment_of_cleaners',
            'concierge_restocking',
            'maintance',
            'safely_insurance_fee',
            'airbnb_cohost_payout',
            'accounting_adjustment',
            'linked_booking_exclude_logic',
        ];

        return $totals;
    }

    /**
     * @param  array{property_id: int, date_range: string, month?: int|string|null, year?: int|string|null}  $filters
     */
    public function exportCsv(User $user, array $filters): StreamedResponse
    {
        $report = $this->report($user, $filters);

        return response()->streamDownload(function () use ($report): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Field', 'Value']);

            foreach ($report as $key => $value) {
                if (is_array($value)) {
                    continue;
                }

                fputcsv($handle, [$key, $value]);
            }

            fclose($handle);
        }, 'owner-statement.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyTotals(Property $property, array $range): array
    {
        return [
            'property' => [
                'id' => $property->id,
                'property_title' => $property->property_title,
            ],
            'date_range' => $range['date_range'],
            'month' => $range['month'],
            'year' => $range['year'],
            'start_date' => $range['start_date'],
            'end_date' => $range['end_date'],
            'accomodations' => 0.0,
            'cleaning_fee' => 0.0,
            'tot_charged_to_guest' => 0.0,
            'total_guest_paid' => 0.0,
            'management_fee' => 0.0,
            'owner_payout' => 0.0,
            'booking_payments' => 0.0,
            'property_payments' => 0.0,
            'total_income' => 0.0,
            'total_expenses' => 0.0,
            'net_income' => 0.0,
            'booked_nights' => 0,
            'blocked_nights' => 0,
            'total_reservations' => 0,
            'occupancy_percentage' => 0.0,
            'average_daily_rate' => 0.0,
            'owner_notes' => [],
        ];
    }

    /**
     * @return array{date_range: string, start_date: string, end_date: string, months: list<string>, month: string|null, year: string|null, use_reservation_dates: bool}
     */
    private function resolveDateRange(string $dateRange, mixed $month, mixed $year): array
    {
        $now = Carbon::now();

        return match ($dateRange) {
            'this_month' => $this->monthRange($now->copy()->startOfMonth(), 'this_month'),
            'last_month' => $this->monthRange($now->copy()->subMonthNoOverflow()->startOfMonth(), 'last_month'),
            'next_month' => $this->monthRange($now->copy()->addMonthNoOverflow()->startOfMonth(), 'next_month'),
            'this_year' => [
                'date_range' => 'this_year',
                'start_date' => $now->copy()->startOfYear()->toDateString(),
                'end_date' => $now->copy()->endOfYear()->toDateString(),
                'months' => [],
                'month' => null,
                'year' => $now->format('Y'),
                'use_reservation_dates' => false,
            ],
            'last_year' => [
                'date_range' => 'last_year',
                'start_date' => $now->copy()->subYear()->startOfYear()->toDateString(),
                'end_date' => $now->copy()->subYear()->endOfYear()->toDateString(),
                'months' => [],
                'month' => null,
                'year' => $now->copy()->subYear()->format('Y'),
                'use_reservation_dates' => false,
            ],
            'last_12_months' => [
                'date_range' => 'last_12_months',
                'start_date' => $now->copy()->subMonthsNoOverflow(12)->startOfMonth()->toDateString(),
                'end_date' => $now->copy()->subMonthNoOverflow()->endOfMonth()->toDateString(),
                'months' => [],
                'month' => null,
                'year' => null,
                'use_reservation_dates' => true,
            ],
            'specific_month' => $this->specificMonthRange($month, $year),
            default => throw ValidationException::withMessages([
                'date_range' => 'Unsupported date range.',
            ]),
        };
    }

    /**
     * @return array{date_range: string, start_date: string, end_date: string, months: list<string>, month: string|null, year: string|null, use_reservation_dates: bool}
     */
    private function monthRange(Carbon $start, string $label): array
    {
        return [
            'date_range' => $label,
            'start_date' => $start->toDateString(),
            'end_date' => $start->copy()->endOfMonth()->toDateString(),
            'months' => [$start->format('m')],
            'month' => $start->format('m'),
            'year' => $start->format('Y'),
            'use_reservation_dates' => false,
        ];
    }

    /**
     * @return array{date_range: string, start_date: string, end_date: string, months: list<string>, month: string|null, year: string|null, use_reservation_dates: bool}
     */
    private function specificMonthRange(mixed $month, mixed $year): array
    {
        if ($month === null || $year === null || $month === '' || $year === '') {
            throw ValidationException::withMessages([
                'month' => 'Month and year are required for specific_month.',
            ]);
        }

        $start = Carbon::createFromDate((int) $year, (int) $month, 1)->startOfMonth();

        return $this->monthRange($start, 'specific_month');
    }

    private function resolveProperty(User $user, int $propertyId): Property
    {
        $property = $this->propertyQueryFor($user)->whereKey($propertyId)->first();

        if ($property === null) {
            throw ValidationException::withMessages([
                'property_id' => 'Property not found or not accessible.',
            ]);
        }

        return $property;
    }

    /**
     * @return Builder<Property>
     */
    private function propertyQueryFor(User $user)
    {
        $query = Property::query()->notDeleted();

        if ($user->isOwner()) {
            $ownerIds = $user->owners()->pluck('owners.id');

            $query->whereHas('owners', fn ($q) => $q->whereIn('owners.id', $ownerIds));
        }

        return $query;
    }
}
