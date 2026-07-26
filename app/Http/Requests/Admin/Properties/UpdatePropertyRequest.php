<?php

namespace App\Http\Requests\Admin\Properties;

use App\Models\Property;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        $property = $this->route('property');

        return $property instanceof Property
            ? ($this->user()?->can('update', $property) ?? false)
            : false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'property_title' => ['sometimes', 'required', 'string', 'max:255'],
            'region_id' => ['nullable', 'integer', 'exists:regions,id'],
            'subregion_id' => ['nullable', 'integer', 'exists:subregions,id'],
            'hostaway_listing_id' => ['nullable', 'integer'],
            'street_address' => ['nullable', 'string'],
            'city' => ['nullable', 'string'],
            'state' => ['nullable', 'string'],
            'zipcode' => ['nullable', 'string'],
            'status' => ['nullable', 'boolean'],
            'owner_ids' => ['nullable', 'array'],
            'owner_ids.*' => ['integer', 'exists:owners,id'],
        ];
    }
}
