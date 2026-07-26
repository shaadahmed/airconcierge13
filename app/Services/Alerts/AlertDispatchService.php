<?php

namespace App\Services\Alerts;

use App\Models\Booking;
use App\Models\Property;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Dispatches scheduled alert keys.
 *
 * Detection bodies mirror legacy CronJobsController checks and log findings.
 * Email/Slack delivery and schema-missing alerts remain follow-ups (see deferred keys).
 */
class AlertDispatchService
{
    public function run(string $alertKey): void
    {
        match ($alertKey) {
            'booking-conflict' => $this->detectBookingConflicts(),
            'anti-gap' => $this->detectAntiGap(),
            'property-vacancy' => $this->detectPropertyVacancies(),
            'booking-month-difference' => $this->detectBookingMonthDifferences(),
            'owner-block-reminders' => $this->detectOwnerBlockReminders(),
            'owner-block-extensions' => $this->detectOwnerBlockExtensions(),
            'consecutive-guest-bookings' => $this->detectConsecutiveGuestBookings(),
            // Follow-up: needs parent_id / expense_incurred_date on payment tables (not in L13 schema yet).
            'recurring-payments' => $this->defer($alertKey, 'booking_payments/property_payments lack recurrence parent columns'),
            // Follow-up: needs pwd_updated_at + password_expiries table; skip owners.status per ADR-009.
            'password-expiry' => $this->defer($alertKey, 'pwd_updated_at / password_expiries schema not ported'),
            // Follow-up: needs properties_permit_information / insurance tables.
            'property-permit-expiry',
            'insurance-policy-expiry',
            'business-license-expiry' => $this->defer($alertKey, 'permit/insurance tables not in live migrations'),
            // Follow-up: L13 booking_alerts uses resolved, not is_triggered/trigger_time.
            'security-deposit' => $this->defer($alertKey, 'booking_alerts trigger columns differ from legacy'),
            // Follow-up: needs cron_logs + property audit visit tables.
            'property-audits',
            'property-audit-reminders' => $this->defer($alertKey, 'cron_logs / property audit schema not ported'),
            // Follow-up: needs month_closing_logs + owner statement PDF/email helpers.
            'owners-payout' => $this->defer($alertKey, 'month_closing_logs + payout email helpers not ported'),
            default => Log::warning('AlertDispatchService received unknown alert key.', [
                'alert_key' => $alertKey,
            ]),
        };
    }

    public function detectBookingConflicts(): int
    {
        $conflicts = 0;

        $bookings = Booking::query()
            ->notDeleted()
            ->where('cancelled_booking', false)
            ->whereNotNull('property_id')
            ->whereNotNull('reservation_start_date')
            ->whereNotNull('reservation_end_date')
            ->orderBy('property_id')
            ->orderBy('reservation_start_date')
            ->get();

        foreach ($bookings->groupBy('property_id') as $propertyBookings) {
            $ordered = $propertyBookings->values();

            for ($i = 0; $i < $ordered->count(); $i++) {
                for ($j = $i + 1; $j < $ordered->count(); $j++) {
                    $a = $ordered[$i];
                    $b = $ordered[$j];

                    if ($a->reservation_start_date < $b->reservation_end_date
                        && $b->reservation_start_date < $a->reservation_end_date) {
                        $conflicts++;
                        Log::warning('Booking conflict detected.', [
                            'property_id' => $a->property_id,
                            'booking_a' => $a->id,
                            'booking_b' => $b->id,
                        ]);
                    }
                }
            }
        }

        Log::info('Booking conflict check complete.', ['conflicts' => $conflicts]);

        return $conflicts;
    }

    public function detectAntiGap(): int
    {
        $today = Carbon::today();
        $startInTwoDays = $today->copy()->addDays(2)->toDateString();
        $fourteenDaysAgo = $today->copy()->subDays(14)->toDateString();
        $twoMonthsAgo = $today->copy()->subMonths(2)->toDateString();

        $upcoming = Booking::query()
            ->notDeleted()
            ->where('cancelled_booking', false)
            ->whereDate('reservation_start_date', $startInTwoDays)
            ->whereHas('property', fn ($q) => $q->notDeleted())
            ->with('property:id,property_title')
            ->get();

        $hits = 0;

        foreach ($upcoming as $booking) {
            $recentlyOccupied = Booking::query()
                ->notDeleted()
                ->where('cancelled_booking', false)
                ->where('property_id', $booking->property_id)
                ->where(function ($query) use ($today, $fourteenDaysAgo): void {
                    $query->where(function ($inner) use ($today, $fourteenDaysAgo): void {
                        $inner->whereDate('reservation_end_date', '>=', $fourteenDaysAgo)
                            ->whereDate('reservation_end_date', '<=', $today->toDateString());
                    })->orWhere(function ($inner) use ($today): void {
                        $inner->whereDate('reservation_start_date', '<=', $today->toDateString())
                            ->whereDate('reservation_end_date', '>=', $today->toDateString());
                    });
                })
                ->exists();

            if ($recentlyOccupied) {
                continue;
            }

            $previous = Booking::query()
                ->notDeleted()
                ->where('cancelled_booking', false)
                ->where('property_id', $booking->property_id)
                ->whereDate('reservation_end_date', '<=', $fourteenDaysAgo)
                ->whereDate('reservation_start_date', '>=', $twoMonthsAgo)
                ->orderByDesc('reservation_end_date')
                ->first();

            if ($previous === null) {
                continue;
            }

            $hits++;
            Log::warning('Anti-gap alert detected.', [
                'property_id' => $booking->property_id,
                'property_title' => $booking->property?->property_title,
                'upcoming_start' => $booking->reservation_start_date?->toDateString(),
                'previous_end' => $previous->reservation_end_date?->toDateString(),
            ]);
        }

        Log::info('Anti-gap check complete.', ['hits' => $hits]);

        return $hits;
    }

    public function detectPropertyVacancies(): int
    {
        $today = Carbon::today()->toDateString();
        $weekAhead = Carbon::today()->addDays(7)->toDateString();

        $occupiedPropertyIds = Booking::query()
            ->notDeleted()
            ->where('cancelled_booking', false)
            ->where(function ($query) use ($today, $weekAhead): void {
                $query->where(function ($inner) use ($today, $weekAhead): void {
                    $inner->whereBetween('reservation_start_date', [$today, $weekAhead])
                        ->orWhereBetween('reservation_end_date', [$today, $weekAhead]);
                })->orWhere(function ($inner) use ($today): void {
                    $inner->whereDate('reservation_start_date', '<=', $today)
                        ->whereDate('reservation_end_date', '>=', $today);
                });
            })
            ->pluck('property_id')
            ->filter()
            ->unique()
            ->all();

        $vacancies = Property::query()
            ->live()
            ->whereNotIn('id', $occupiedPropertyIds)
            ->where(function ($query) use ($today): void {
                $query->where(function ($inner) use ($today): void {
                    $inner->whereDate('contract_end_date', '>=', $today)
                        ->whereDate('contract_start_date', '<=', $today);
                })->orWhereNull('contract_end_date');
            })
            ->get(['id', 'property_title', 'street_address', 'city', 'state']);

        foreach ($vacancies as $property) {
            Log::warning('Property vacancy detected.', [
                'property_id' => $property->id,
                'property_title' => $property->property_title,
            ]);
        }

        Log::info('Property vacancy check complete.', ['vacancies' => $vacancies->count()]);

        return $vacancies->count();
    }

    public function detectBookingMonthDifferences(): int
    {
        $year = (int) date('Y');

        $bookings = Booking::query()
            ->notDeleted()
            ->where('year', $year)
            ->whereNotNull('month')
            ->whereNotNull('reservation_start_date')
            ->get(['id', 'month', 'reservation_start_date']);

        $hits = 0;

        foreach ($bookings as $booking) {
            $startMonth = (int) $booking->reservation_start_date->format('n');
            $month = (int) $booking->month;

            if ($month !== $startMonth && $month !== $startMonth + 1) {
                $hits++;
                Log::warning('Booking month difference detected.', [
                    'booking_id' => $booking->id,
                    'month' => $month,
                    'reservation_start_month' => $startMonth,
                ]);
            }
        }

        Log::info('Booking month difference check complete.', ['hits' => $hits]);

        return $hits;
    }

    public function detectOwnerBlockReminders(): int
    {
        $today = Carbon::today();
        $targetStart = $today->copy()->addDays(10)->toDateString();

        $blocks = Booking::query()
            ->notDeleted()
            ->where('platform_id', 7)
            ->whereDate('reservation_start_date', $targetStart)
            ->whereNotNull('booking_date')
            ->whereHas('property', fn ($q) => $q->live())
            ->with('property:id,property_title,supportemail')
            ->get();

        $hits = 0;

        foreach ($blocks as $block) {
            $leadDays = $block->booking_date->diffInDays($block->reservation_start_date, false);

            if ($leadDays <= 10) {
                continue;
            }

            $hits++;
            Log::warning('Owner block reminder detected.', [
                'booking_id' => $block->id,
                'property_id' => $block->property_id,
                'reservation_start_date' => $block->reservation_start_date?->toDateString(),
            ]);
        }

        Log::info('Owner block reminder check complete.', ['hits' => $hits]);

        return $hits;
    }

    public function detectOwnerBlockExtensions(): int
    {
        $today = Carbon::today();

        $blocks = Booking::query()
            ->notDeleted()
            ->where('platform_id', 7)
            ->whereNotNull('reservation_start_date')
            ->whereNotNull('reservation_end_date')
            ->whereHas('property', fn ($q) => $q->notDeleted())
            ->get();

        $hits = 0;

        foreach ($blocks as $block) {
            $daysUntilStart = $today->diffInDays($block->reservation_start_date, false);
            $stayLength = $block->reservation_start_date->diffInDays($block->reservation_end_date);

            if ($daysUntilStart <= 29 || $stayLength < 10) {
                continue;
            }

            $hits++;
            Log::warning('Owner block extension candidate detected.', [
                'booking_id' => $block->id,
                'property_id' => $block->property_id,
                'days_until_start' => $daysUntilStart,
                'stay_length' => $stayLength,
            ]);
        }

        Log::info('Owner block extension check complete.', ['hits' => $hits]);

        return $hits;
    }

    public function detectConsecutiveGuestBookings(): int
    {
        $windowStart = Carbon::now()->subMonths(2)->toDateString();
        $windowEnd = Carbon::now()->addDays(5)->toDateString();

        $bookings = Booking::query()
            ->notDeleted()
            ->where('cancelled_booking', false)
            ->whereDate('reservation_start_date', '>=', $windowStart)
            ->whereDate('reservation_start_date', '<=', $windowEnd)
            ->whereNotNull('property_id')
            ->with(['guests:id'])
            ->orderBy('property_id')
            ->orderBy('reservation_start_date')
            ->get();

        $hits = 0;

        /** @var Collection<string, Collection<int, Booking>> $byPropertyGuest */
        $byPropertyGuest = collect();

        foreach ($bookings as $booking) {
            foreach ($booking->guests as $guest) {
                $key = $booking->property_id.'-'.$guest->id;
                $group = $byPropertyGuest->get($key, collect());
                $group->push($booking);
                $byPropertyGuest->put($key, $group);
            }
        }

        foreach ($byPropertyGuest as $key => $group) {
            $ordered = $group->sortBy(fn (Booking $b) => $b->reservation_start_date?->timestamp ?? 0)->values();

            for ($i = 0; $i < $ordered->count() - 1; $i++) {
                $current = $ordered[$i];
                $next = $ordered[$i + 1];

                if ($current->reservation_end_date === null || $next->reservation_start_date === null) {
                    continue;
                }

                $gapStart = $current->reservation_end_date->copy()->addDay()->toDateString();
                $isConsecutive = $next->reservation_start_date->toDateString() === $gapStart
                    || $next->reservation_start_date->lte($current->reservation_end_date);

                if (! $isConsecutive) {
                    continue;
                }

                $totalNights = $current->reservation_start_date->diffInDays($next->reservation_end_date ?? $next->reservation_start_date);

                if ($totalNights <= 30) {
                    continue;
                }

                $hits++;
                Log::warning('Consecutive guest booking alert detected.', [
                    'key' => $key,
                    'booking_a' => $current->id,
                    'booking_b' => $next->id,
                    'total_nights' => $totalNights,
                ]);
            }
        }

        Log::info('Consecutive guest booking check complete.', ['hits' => $hits]);

        return $hits;
    }

    private function defer(string $alertKey, string $reason): void
    {
        Log::info('AlertDispatchService deferred alert key.', [
            'alert_key' => $alertKey,
            'reason' => $reason,
        ]);
    }
}
