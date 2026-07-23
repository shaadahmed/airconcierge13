<?php

namespace App\Services\Reports;

use App\Models\Booking;
use App\Models\Property;
use App\Models\PropertyMonthlyMetric;
use App\Models\Region;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * @param  array{year?: int, month?: int, region_id?: int, property_id?: int}  $filters
     * @return Collection<int, object>
     */
    public function bookingSummary(array $filters = []): Collection
    {
        return Booking::query()
            ->notDeleted()
            ->when($filters['property_id'] ?? null, fn ($q, int $id) => $q->where('property_id', $id))
            ->when($filters['region_id'] ?? null, fn ($q, int $id) => $q->where('region_id', $id))
            ->when($filters['year'] ?? null, fn ($q, int $year) => $q->whereYear('reservation_start_date', $year))
            ->when($filters['month'] ?? null, fn ($q, int $month) => $q->whereMonth('reservation_start_date', $month))
            ->select([
                'property_id',
                DB::raw('COUNT(*) as booking_count'),
                DB::raw('SUM(total_guest_paid) as revenue'),
                DB::raw('SUM(CASE WHEN cancelled_booking = 1 THEN 1 ELSE 0 END) as cancelled_count'),
            ])
            ->groupBy('property_id')
            ->get();
    }

    /**
     * @param  array{year?: int, region_id?: int}  $filters
     * @return Collection<int, PropertyMonthlyMetric>
     */
    public function propertyMetrics(array $filters = []): Collection
    {
        return PropertyMonthlyMetric::query()
            ->when($filters['year'] ?? null, fn ($q, int $year) => $q->where('year', $year))
            ->when($filters['region_id'] ?? null, function ($q, int $regionId): void {
                $q->whereIn('property_id', Property::query()->where('region_id', $regionId)->pluck('id'));
            })
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->get();
    }

    /**
     * TOT (transient occupancy tax) style rollup by region.
     *
     * @param  array{year?: int}  $filters
     * @return Collection<int, object>
     */
    public function totReport(array $filters = []): Collection
    {
        return Booking::query()
            ->notDeleted()
            ->where('cancelled_booking', false)
            ->when($filters['year'] ?? null, fn ($q, int $year) => $q->whereYear('reservation_start_date', $year))
            ->select([
                'region_id',
                DB::raw('SUM(tot_charged_to_guest) as tot_total'),
                DB::raw('COUNT(*) as booking_count'),
            ])
            ->groupBy('region_id')
            ->get();
    }

    /**
     * @return Collection<int, Region>
     */
    public function regions(): Collection
    {
        return Region::query()->notDeleted()->orderBy('region_name')->get();
    }
}
