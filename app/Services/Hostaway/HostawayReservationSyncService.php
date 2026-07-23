<?php

namespace App\Services\Hostaway;

use App\Enums\HostawayReservationLogStatus;
use App\Models\Booking;
use App\Models\HostawayReservationLog;
use App\Services\Bookings\BookingService;
use Illuminate\Support\Facades\Log;

/**
 * Orchestrates Hostaway webhook payloads into reservation logs.
 *
 * Booking create/update/cancel are applied through BookingService.
 */
class HostawayReservationSyncService
{
    public function __construct(private BookingService $bookingService) {}

    private const STATUS_PENDING = 'pending';

    private const STATUS_NEW = 'new';

    private const STATUS_MODIFIED = 'modified';

    private const STATUS_CANCELLED = 'cancelled';

    private const PAYMENT_STATUS_PAID = 'paid';

    /**
     * @param  array<string, mixed>  $payload
     */
    public function handle(array $payload): void
    {
        $reservation = $this->extractReservation($payload);

        if ($reservation === null) {
            Log::info('Hostaway sync skipped: empty or unrecognized payload.');

            return;
        }

        $status = strtolower((string) ($reservation['status'] ?? ''));
        $paymentStatus = strtolower((string) ($reservation['paymentStatus'] ?? ''));

        if ($status === self::STATUS_PENDING && $paymentStatus === self::PAYMENT_STATUS_PAID) {
            $this->create($reservation);

            return;
        }

        if ($status === self::STATUS_NEW) {
            $this->create($reservation);

            return;
        }

        if ($status === self::STATUS_MODIFIED) {
            $this->modify($reservation);

            return;
        }

        if ($status === self::STATUS_CANCELLED) {
            $this->cancel($reservation);
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>|null
     */
    private function extractReservation(array $payload): ?array
    {
        if ($payload === []) {
            return null;
        }

        if (isset($payload['result']) && is_array($payload['result'])) {
            /** @var array<string, mixed> $result */
            $result = $payload['result'];

            return $result;
        }

        if (isset($payload['reservation']) && is_array($payload['reservation'])) {
            /** @var array<string, mixed> $reservation */
            $reservation = $payload['reservation'];

            return $reservation;
        }

        if (isset($payload['id'], $payload['status'])) {
            return $payload;
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $reservation
     */
    private function create(array $reservation): void
    {
        $this->process(
            $reservation,
            HostawayReservationLogStatus::Processed,
            [
                HostawayReservationLogStatus::Processed,
                HostawayReservationLogStatus::BookingCreateInProgress,
            ],
            fn () => $this->bookingService->createFromHostawayReservation($reservation),
        );
    }

    /**
     * @param  array<string, mixed>  $reservation
     */
    private function modify(array $reservation): void
    {
        $this->process(
            $reservation,
            HostawayReservationLogStatus::BookingUpdateSuccess,
            [
                HostawayReservationLogStatus::BookingUpdateSuccess,
                HostawayReservationLogStatus::BookingUpdateInProgress,
                HostawayReservationLogStatus::Processed,
            ],
            fn () => $this->bookingService->updateFromHostawayReservation($reservation),
        );
    }

    /**
     * @param  array<string, mixed>  $reservation
     */
    private function cancel(array $reservation): void
    {
        $this->process(
            $reservation,
            HostawayReservationLogStatus::Cancelled,
            [
                HostawayReservationLogStatus::Cancelled,
                HostawayReservationLogStatus::CancellationInProgress,
            ],
            fn () => $this->bookingService->cancelFromHostawayReservation($reservation),
        );
    }

    /**
     * @param  array<string, mixed>  $reservation
     * @param  list<HostawayReservationLogStatus>  $idempotentStatuses
     * @param  callable(): Booking  $operation
     */
    private function process(
        array $reservation,
        HostawayReservationLogStatus $successStatus,
        array $idempotentStatuses,
        callable $operation,
    ): void {
        $reservationId = isset($reservation['id']) ? (int) $reservation['id'] : 0;

        if ($reservationId === 0) {
            Log::info('Hostaway sync skipped: reservation id missing.');

            return;
        }

        $existing = HostawayReservationLog::query()
            ->forReservation($reservationId)
            ->first();

        if ($existing !== null && in_array($existing->status, $idempotentStatuses, true)) {
            Log::info('Hostaway sync skipped (idempotent).', [
                'reservation_id' => $reservationId,
                'status' => $existing->status->value,
            ]);

            return;
        }

        $bookingCode = isset($reservation['confirmationCode'])
            ? (string) $reservation['confirmationCode']
            : (string) $reservationId;
        $guestName = isset($reservation['guestName']) ? (string) $reservation['guestName'] : null;
        $encodedPayload = json_encode($reservation);

        try {
            $booking = $operation();
            $attributes = [
                'reservation_id' => $reservationId,
                'booking_id' => $booking->id,
                'booking_code' => $bookingCode,
                'guest_name' => $guestName,
                'status' => $successStatus,
                'log_type' => HostawayReservationLog::LOG_TYPE_BOOKING,
                'comments' => null,
                'hostaway_response' => $encodedPayload === false ? null : $encodedPayload,
            ];
            if ($existing === null) {
                HostawayReservationLog::query()->create($attributes);
            } else {
                $existing->update($attributes);
            }
        } catch (\Throwable $exception) {
            $attributes = [
                'booking_code' => $bookingCode,
                'guest_name' => $guestName,
                'status' => HostawayReservationLogStatus::Failed,
                'log_type' => HostawayReservationLog::LOG_TYPE_BOOKING,
                'comments' => $exception->getMessage(),
                'hostaway_response' => $encodedPayload === false ? null : $encodedPayload,
            ];
            if ($existing === null) {
                HostawayReservationLog::query()->create(['reservation_id' => $reservationId, ...$attributes]);
            } else {
                $existing->update($attributes);
            }

            Log::warning('Hostaway booking sync failed.', [
                'reservation_id' => $reservationId,
                'exception' => $exception->getMessage(),
            ]);
        }
    }
}
