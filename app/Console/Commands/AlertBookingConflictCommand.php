<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Illuminate\Console\Command;

class AlertBookingConflictCommand extends Command
{
    protected $signature = 'alert:booking-conflict';

    protected $description = 'Detect overlapping non-cancelled bookings on the same property.';

    public function handle(): int
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
                        $this->warn("Conflict property {$a->property_id}: bookings {$a->id} and {$b->id}");
                    }
                }
            }
        }

        $this->info("Found {$conflicts} booking conflict pair(s).");

        return self::SUCCESS;
    }
}
