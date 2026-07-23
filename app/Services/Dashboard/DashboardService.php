<?php

namespace App\Services\Dashboard;

use App\Models\Booking;
use App\Models\Owner;
use App\Models\Property;
use App\Models\User;
use Illuminate\Support\Carbon;

class DashboardService
{
    /**
     * @return array{bookings_this_month: int, active_properties: int, owners: int, cancelled_this_month: int, revenue_this_month: float}
     */
    public function summaryStats(?Carbon $now = null): array
    {
        $now ??= now();

        return [
            'bookings_this_month' => Booking::query()
                ->notDeleted()
                ->whereMonth('reservation_start_date', $now->month)
                ->whereYear('reservation_start_date', $now->year)
                ->count(),
            'cancelled_this_month' => Booking::query()
                ->notDeleted()
                ->where('cancelled_booking', true)
                ->whereMonth('reservation_start_date', $now->month)
                ->whereYear('reservation_start_date', $now->year)
                ->count(),
            'active_properties' => Property::query()->live()->count(),
            'owners' => Owner::query()->notDeleted()->count(),
            'revenue_this_month' => (float) Booking::query()
                ->notDeleted()
                ->where('cancelled_booking', false)
                ->whereMonth('reservation_start_date', $now->month)
                ->whereYear('reservation_start_date', $now->year)
                ->sum('total_guest_paid'),
        ];
    }

    /**
     * @return array{labels: list<string>, values: list<float>}
     */
    public function revenueChart(int $months = 6): array
    {
        $labels = [];
        $values = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $point = now()->subMonths($i);
            $labels[] = $point->format('Y-m');
            $values[] = (float) Booking::query()
                ->notDeleted()
                ->where('cancelled_booking', false)
                ->whereMonth('reservation_start_date', $point->month)
                ->whereYear('reservation_start_date', $point->year)
                ->sum('total_guest_paid');
        }

        return ['labels' => $labels, 'values' => $values];
    }

    /**
     * @return array{name: string, email: string, role: string}
     */
    public function profile(User $user): array
    {
        return [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role?->value ?? '',
        ];
    }

    /**
     * Owner statement placeholder data until full statement engine lands.
     *
     * @return array{owner_id: int, booking_count: int, payout_total: float}
     */
    public function ownerStatementSummary(Owner $owner): array
    {
        $propertyIds = $owner->properties()->pluck('properties.id');

        $bookings = Booking::query()
            ->notDeleted()
            ->whereIn('property_id', $propertyIds)
            ->where('cancelled_booking', false);

        return [
            'owner_id' => $owner->id,
            'booking_count' => (clone $bookings)->count(),
            'payout_total' => (float) (clone $bookings)->sum('owner_payout_amount_from_airconcierge'),
        ];
    }
}
