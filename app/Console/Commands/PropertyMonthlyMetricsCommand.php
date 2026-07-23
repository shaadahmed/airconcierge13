<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\PropertyMonthlyMetric;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PropertyMonthlyMetricsCommand extends Command
{
    protected $signature = 'property:monthly-metrics {--full : Recalculate all months present in bookings}';

    protected $description = 'Recalculate property monthly metrics from bookings.';

    public function handle(): int
    {
        $query = Booking::query()
            ->notDeleted()
            ->where('cancelled_booking', false)
            ->whereNotNull('property_id')
            ->whereNotNull('reservation_start_date');

        if (! $this->option('full')) {
            $query->whereMonth('reservation_start_date', now()->month)
                ->whereYear('reservation_start_date', now()->year);
        }

        $rows = $query
            ->select([
                'property_id',
                DB::raw('YEAR(reservation_start_date) as year'),
                DB::raw('MONTH(reservation_start_date) as month'),
                DB::raw('SUM(total_guest_paid) as revenue'),
                DB::raw('SUM(COALESCE(no_of_nights, 0)) as nights_booked'),
                DB::raw('COUNT(*) as booking_count'),
            ])
            ->groupBy('property_id', DB::raw('YEAR(reservation_start_date)'), DB::raw('MONTH(reservation_start_date)'))
            ->get();

        foreach ($rows as $row) {
            PropertyMonthlyMetric::query()->updateOrCreate(
                [
                    'property_id' => $row->property_id,
                    'year' => $row->year,
                    'month' => $row->month,
                ],
                [
                    'revenue' => $row->revenue ?? 0,
                    'nights_booked' => $row->nights_booked ?? 0,
                    'occupancy' => 0,
                ],
            );
        }

        $this->info('Updated '.$rows->count().' property monthly metric row(s).');

        return self::SUCCESS;
    }
}
