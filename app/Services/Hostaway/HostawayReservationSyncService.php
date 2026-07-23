<?php

namespace App\Services\Hostaway;

use App\Enums\HostawayReservationLogStatus;
use App\Models\HostawayReservationLog;
use Illuminate\Support\Facades\Log;

/**
 * Orchestrates Hostaway webhook payloads into reservation logs.
 *
 * Booking create/update/cancel are stubbed until Phase 2.3 (ADR-011).
 */
class HostawayReservationSyncService
{
    public const DEFERRED_COMMENT = 'Awaiting BookingService (Phase 2.3) — booking mutation deferred.';

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
            $this->stubCreate($reservation);

            return;
        }

        if ($status === self::STATUS_NEW) {
            $this->stubCreate($reservation);

            return;
        }

        if ($status === self::STATUS_MODIFIED) {
            $this->stubModify($reservation);

            return;
        }

        if ($status === self::STATUS_CANCELLED) {
            $this->stubCancel($reservation);
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
    private function stubCreate(array $reservation): void
    {
        $this->recordDeferredStub(
            $reservation,
            HostawayReservationLogStatus::BookingCreateInProgress,
            [HostawayReservationLogStatus::Processed, HostawayReservationLogStatus::BookingCreateInProgress],
        );
    }

    /**
     * @param  array<string, mixed>  $reservation
     */
    private function stubModify(array $reservation): void
    {
        $this->recordDeferredStub(
            $reservation,
            HostawayReservationLogStatus::BookingUpdateInProgress,
            [
                HostawayReservationLogStatus::BookingUpdateSuccess,
                HostawayReservationLogStatus::BookingUpdateInProgress,
                HostawayReservationLogStatus::Processed,
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $reservation
     */
    private function stubCancel(array $reservation): void
    {
        $this->recordDeferredStub(
            $reservation,
            HostawayReservationLogStatus::CancellationInProgress,
            [
                HostawayReservationLogStatus::Cancelled,
                HostawayReservationLogStatus::CancellationInProgress,
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $reservation
     * @param  list<HostawayReservationLogStatus>  $idempotentStatuses
     */
    private function recordDeferredStub(
        array $reservation,
        HostawayReservationLogStatus $inProgressStatus,
        array $idempotentStatuses,
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

        if ($existing === null) {
            HostawayReservationLog::query()->create([
                'reservation_id' => $reservationId,
                'booking_code' => $bookingCode,
                'guest_name' => $guestName,
                'status' => $inProgressStatus,
                'log_type' => HostawayReservationLog::LOG_TYPE_BOOKING,
                'comments' => self::DEFERRED_COMMENT,
                'hostaway_response' => $encodedPayload === false ? null : $encodedPayload,
            ]);

            return;
        }

        $existing->update([
            'booking_code' => $bookingCode,
            'guest_name' => $guestName,
            'status' => $inProgressStatus,
            'comments' => self::DEFERRED_COMMENT,
            'hostaway_response' => $encodedPayload === false ? null : $encodedPayload,
        ]);
    }
}
