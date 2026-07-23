<?php

namespace App\Models;

use App\Enums\HostawayReservationLogStatus;
use Database\Factories\HostawayReservationLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property HostawayReservationLogStatus $status
 * @property int $reservation_id
 * @property int|null $booking_id
 */
#[Fillable([
    'reservation_id',
    'booking_id',
    'booking_code',
    'guest_name',
    'status',
    'log_type',
    'comments',
    'hostaway_response',
    'difference_array',
])]
class HostawayReservationLog extends Model
{
    /** @use HasFactory<HostawayReservationLogFactory> */
    use HasFactory;

    public const LOG_TYPE_BOOKING = 'booking';

    public const LOG_TYPE_REVIEW = 'review';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'reservation_id' => 'integer',
            'booking_id' => 'integer',
            'status' => HostawayReservationLogStatus::class,
        ];
    }

    public function isSuccessfullyProcessed(): bool
    {
        return in_array($this->status, HostawayReservationLogStatus::successStatuses(), true);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeForReservation(Builder $query, int $reservationId, string $logType = self::LOG_TYPE_BOOKING): Builder
    {
        return $query->where('reservation_id', $reservationId)->where('log_type', $logType);
    }
}
