<?php

namespace App\Http\Requests\Admin\Imports;

use App\Models\Booking;
use Illuminate\Foundation\Http\FormRequest;

class ImportPropertiesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Booking::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'rows' => ['required', 'array'],
            'rows.*.property_title' => ['required', 'string'],
            'rows.*.region_id' => ['nullable', 'integer'],
            'rows.*.street_address' => ['nullable', 'string'],
            'rows.*.city' => ['nullable', 'string'],
            'rows.*.state' => ['nullable', 'string'],
            'rows.*.zipcode' => ['nullable', 'string'],
            'rows.*.hostaway_listing_id' => ['nullable', 'integer'],
        ];
    }
}
