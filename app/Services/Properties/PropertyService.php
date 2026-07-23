<?php

namespace App\Services\Properties;

use App\Models\Property;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PropertyService
{
    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, Property>
     */
    public function list(array $filters = []): Collection
    {
        return Property::query()
            ->notDeleted()
            ->with(['region', 'subregion', 'owners'])
            ->when($filters['region_id'] ?? null, fn ($q, int $id) => $q->where('region_id', $id))
            ->when(array_key_exists('status', $filters), fn ($q) => $q->where('status', (bool) $filters['status']))
            ->orderBy('property_title')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Property
    {
        return DB::transaction(function () use ($attributes): Property {
            $property = Property::query()->create([
                ...$attributes,
                'created_date' => now(),
                'modified_date' => now(),
                'deleted' => false,
            ]);

            if (isset($attributes['owner_ids']) && is_array($attributes['owner_ids'])) {
                $property->owners()->sync($attributes['owner_ids']);
            }

            return $property->load(['owners', 'region']);
        });
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Property $property, array $attributes): Property
    {
        return DB::transaction(function () use ($property, $attributes): Property {
            $property->update([
                ...collect($attributes)->except('owner_ids')->all(),
                'modified_date' => now(),
            ]);

            if (array_key_exists('owner_ids', $attributes)) {
                $property->owners()->sync($attributes['owner_ids'] ?? []);
            }

            return $property->fresh(['owners', 'region']) ?? $property;
        });
    }

    public function deactivate(Property $property): Property
    {
        $property->update([
            'status' => false,
            'inactive_date' => now()->toDateString(),
            'modified_date' => now(),
        ]);

        return $property->fresh() ?? $property;
    }

    public function delete(Property $property): Property
    {
        $property->update([
            'deleted' => true,
            'modified_date' => now(),
        ]);

        return $property->fresh() ?? $property;
    }
}
