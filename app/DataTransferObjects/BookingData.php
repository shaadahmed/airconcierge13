<?php

namespace App\DataTransferObjects;

use Illuminate\Support\Arr;

/**
 * Reference DTO for booking create payloads.
 *
 * Prefer typed DTOs over raw attribute arrays when refining other services —
 * keep them immutable-by-convention carriers with no business logic.
 */
final class BookingData
{
    /**
     * @param  array<string, mixed>  $attributes
     * @param  list<int>  $guestIds
     */
    public function __construct(
        public readonly array $attributes,
        public readonly array $guestIds = [],
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromArray(array $payload): self
    {
        $guestIds = $payload['guest_ids'] ?? [];

        /** @var list<int> $guestIds */
        $guestIds = is_array($guestIds) ? array_values($guestIds) : [];

        return new self(
            attributes: Arr::except($payload, ['guest_ids']),
            guestIds: $guestIds,
        );
    }
}
