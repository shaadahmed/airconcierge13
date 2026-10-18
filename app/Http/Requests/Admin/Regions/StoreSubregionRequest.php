<?php

namespace App\Http\Requests\Admin\Regions;

use App\Enums\BookingLimitType;
use App\Models\Subregion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSubregionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Subregion::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'region_id' => ['required', 'integer', 'exists:regions,id'],
            'name' => ['required', 'string', 'max:255'],
            'transient_occupancy_tax' => ['nullable', 'numeric', 'between:0,99.99'],
            'short_stay_len' => ['nullable', 'integer', 'min:1', 'max:31'],
            'limited_bookings' => ['nullable', 'in:Yes,No'],
            'limit_type' => ['nullable', 'required_if:limited_bookings,Yes', Rule::enum(BookingLimitType::class)],
            'limit_value' => ['nullable', 'integer', 'min:1'],
            'nights_limit' => ['nullable', 'integer', 'min:1'],
            'bookings_limit' => ['nullable', 'integer', 'min:1'],
            'limit_filter' => ['nullable', 'string', 'max:50'],
            'business_license_account' => ['nullable', 'integer', 'in:0,1'],
            'permit_no' => ['nullable', 'string', 'max:30'],
            'permit_issue_date' => ['nullable', 'date'],
            'permit_expiry_date' => ['nullable', 'date', 'after_or_equal:permit_issue_date'],
            'permit_length' => ['nullable', 'integer', 'min:1', 'max:24'],
        ];
    }
}
