<?php

namespace App\Services\Bookings;

use App\Models\Booking;
use App\Models\Guest;
use App\Models\OwnerEmailNotificationLog;
use App\Models\Property;
use App\Services\Email\EmailService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class BookingService
{
    public function __construct(private EmailService $emailService) {}

    /** @param array<string, mixed> $filters @return Collection<int, Booking> */
    public function list(array $filters = []): Collection
    {
        return Booking::query()->notDeleted()->with(['property', 'guests'])
            ->when($filters['property_id'] ?? null, fn ($query, int $id) => $query->where('property_id', $id))
            ->when($filters['cancelled'] ?? null, fn ($query, mixed $cancelled) => $query->where('cancelled_booking', (bool) $cancelled))
            ->latest('id')->get();
    }

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): Booking
    {
        return DB::transaction(function () use ($attributes): Booking {
            $guestIds = $attributes['guest_ids'] ?? [];
            $booking = Booking::query()->create([
                ...collect($attributes)->except('guest_ids')->all(),
                'dateadded' => now(),
                'deleted' => $attributes['deleted'] ?? false,
                'cancelled_booking' => $attributes['cancelled_booking'] ?? false,
            ]);
            $this->syncGuests($booking, $guestIds);

            return $booking->load(['property', 'guests']);
        });
    }

    /** @param array<string, mixed> $attributes */
    public function update(Booking $booking, array $attributes): Booking
    {
        return DB::transaction(function () use ($booking, $attributes): Booking {
            $booking->update(collect($attributes)->except('guest_ids')->all());
            if (array_key_exists('guest_ids', $attributes)) {
                $this->syncGuests($booking, $attributes['guest_ids']);
            }

            return $booking->load(['property', 'guests']);
        });
    }

    public function cancel(Booking $booking): Booking
    {
        $booking->update(['cancelled_booking' => true]);

        return $booking->fresh() ?? $booking;
    }

    public function delete(Booking $booking): Booking
    {
        $booking->update(['deleted' => true]);

        return $booking->fresh() ?? $booking;
    }

    public function restore(Booking $booking): Booking
    {
        $booking->update(['deleted' => false]);

        return $booking->fresh() ?? $booking;
    }

    /** @param array<string, mixed> $reservation */
    public function createFromHostawayReservation(array $reservation): Booking
    {
        $reservationId = (int) ($reservation['id'] ?? 0);
        $property = $this->propertyForReservation($reservation);
        $guest = $this->upsertGuest($reservation);
        $booking = Booking::query()->updateOrCreate(
            ['hostaway_reservation_id' => $reservationId],
            $this->hostawayAttributes($reservation, $property),
        );
        $booking->guests()->syncWithoutDetaching([$guest->id]);
        if ($booking->wasRecentlyCreated) {
            $this->notifyOwnersOnCreate($booking->load('property.owners'));
        }

        return $booking;
    }

    /** @param array<string, mixed> $reservation */
    public function updateFromHostawayReservation(array $reservation): Booking
    {
        return $this->createFromHostawayReservation($reservation);
    }

    /** @param array<string, mixed> $reservation */
    public function cancelFromHostawayReservation(array $reservation): Booking
    {
        $booking = Booking::query()->where('hostaway_reservation_id', (int) ($reservation['id'] ?? 0))->first();
        if ($booking === null) {
            return $this->cancel($this->createFromHostawayReservation($reservation));
        }

        return $this->cancel($booking);
    }

    public function notifyOwnersOnCreate(Booking $booking): void
    {
        foreach ($booking->property?->owners ?? [] as $owner) {
            if ($owner->owner_email === null || $owner->emailstatus !== 1) {
                continue;
            }
            $this->emailService->send(['to' => $owner->owner_email, 'subject' => 'New booking '.$booking->booking_code, 'body' => 'A new booking has been created.', 'source' => 'booking']);
            OwnerEmailNotificationLog::query()->create(['booking_id' => $booking->id, 'owner_id' => $owner->id, 'email_type' => 'booking_created', 'status' => 'sent']);
        }
    }

    /** @param array<string, mixed> $reservation */
    private function propertyForReservation(array $reservation): Property
    {
        $listingId = (int) ($reservation['listingMapId'] ?? $reservation['listingId'] ?? 0);

        return Property::query()->where('hostaway_listing_id', $listingId)->first()
            ?? throw new RuntimeException("Property not found for Hostaway listing {$listingId}.");
    }

    /** @param array<string, mixed> $reservation */
    private function upsertGuest(array $reservation): Guest
    {
        $email = isset($reservation['guestEmail']) ? (string) $reservation['guestEmail'] : null;
        $guestName = (string) ($reservation['guestName'] ?? 'Unknown guest');
        $attributes = [
            'guest_name' => $guestName,
            'first_name' => $reservation['guestFirstName'] ?? null,
            'last_name' => $reservation['guestLastName'] ?? null,
            'phone' => $reservation['guestPhone'] ?? null,
            'email' => $email,
            'deleted' => false,
        ];

        if ($email !== null && $email !== '') {
            return Guest::query()->updateOrCreate(['email' => $email], $attributes);
        }

        return Guest::query()->create($attributes);
    }

    /** @param array<string, mixed> $reservation @return array<string, mixed> */
    private function hostawayAttributes(array $reservation, Property $property): array
    {
        return ['property_id' => $property->id, 'region_id' => $property->region_id, 'subregion_id' => $property->subregion_id, 'hostaway_reservation_id' => (int) $reservation['id'], 'booking_code' => $reservation['confirmationCode'] ?? null, 'reservation_start_date' => $reservation['arrivalDate'] ?? null, 'reservation_end_date' => $reservation['departureDate'] ?? null, 'booking_date' => $reservation['reservationDate'] ?? now()->toDateString(), 'no_of_guests' => $reservation['guests'] ?? $reservation['numberOfGuests'] ?? null, 'cancelled_booking' => strtolower((string) ($reservation['status'] ?? '')) === 'cancelled', 'deleted' => false, 'dateadded' => now()];
    }

    /** @param list<int> $guestIds */
    private function syncGuests(Booking $booking, array $guestIds): void
    {
        $booking->guests()->sync($guestIds);
    }
}
