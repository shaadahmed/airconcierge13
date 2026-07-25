<?php

namespace App\Http\Requests\Admin\Documents;

use App\Models\Booking;
use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'ownerspecific' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'integer'],
            'signid' => ['nullable', 'string', 'max:255'],
            'zohoactionid' => ['nullable', 'string', 'max:255'],
            'roletitle' => ['nullable', 'string', 'max:255'],
            'region_ids' => ['nullable', 'array'],
            'region_ids.*' => ['integer'],
            'subregion_ids' => ['nullable', 'array'],
            'subregion_ids.*' => ['integer'],
            'owner_ids' => ['nullable', 'array'],
            'owner_ids.*' => ['integer'],
            'file' => ['nullable', 'file', 'max:10240'],
        ];
    }
}
