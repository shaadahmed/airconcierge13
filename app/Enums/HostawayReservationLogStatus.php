<?php

namespace App\Enums;

/**
 * Hostaway reservation log statuses (legacy integer values preserved).
 */
enum HostawayReservationLogStatus: int
{
    case Failed = 0;
    case Processed = 1;
    case PropertyNotFound = 2;
    case DuplicateBooking = 3;
    case PlatformNotFound = 4;
    case ReservationNotFetched = 5;
    case Cancelled = 6;
    case CancellationFailed = 7;
    case ReviewReceived = 8;
    case ReviewEmailed = 9;
    case ReviewEmailFailed = 10;
    case BookingUpdateFailed = 11;
    case BookingUpdateSuccess = 12;
    case BookingCreateInProgress = 13;
    case BookingUpdateInProgress = 14;
    case CancellationInProgress = 15;

    public function label(): string
    {
        return match ($this) {
            self::Failed => 'Failed',
            self::Processed => 'Processed',
            self::PropertyNotFound => 'Property Not Found',
            self::DuplicateBooking => 'Duplicate Booking',
            self::PlatformNotFound => 'Platform Not Found',
            self::ReservationNotFetched => 'Reservation Not Fetched',
            self::Cancelled => 'Cancelled',
            self::CancellationFailed => 'Cancellation Failed',
            self::ReviewReceived => 'Review Received',
            self::ReviewEmailed => 'Review Emailed',
            self::ReviewEmailFailed => 'Review Email Failed',
            self::BookingUpdateFailed => 'Booking Update Failed',
            self::BookingUpdateSuccess => 'Booking Update Success',
            self::BookingCreateInProgress => 'Booking Create In Progress',
            self::BookingUpdateInProgress => 'Booking Update In Progress',
            self::CancellationInProgress => 'Cancellation In Progress',
        };
    }

    /**
     * @return list<self>
     */
    public static function successStatuses(): array
    {
        return [
            self::Processed,
            self::Cancelled,
            self::ReviewEmailed,
            self::BookingUpdateSuccess,
        ];
    }
}
