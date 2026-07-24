<?php

namespace App\Services\Alerts;

use App\Models\Booking;
use Illuminate\Support\Facades\Log;

/**
 * Dispatches scheduled alert keys.
 *
 * Implemented alerts run domain logic; others log a follow-up stub (no silent business inventing).
 */
class AlertDispatchService
{
    public function run(string $alertKey): void
    {
        if ($alertKey === 'booking-conflict') {
            $this->detectBookingConflicts();

            return;
        }

        // Follow-up card: port remaining alert domain bodies from legacy CronJobsController.
        Log::info('AlertDispatchService stub executed.', [
            'alert_key' => $alertKey,
            'note' => 'Domain body deferred — Schedule→Job pipeline is live.',
        ]);
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
}
