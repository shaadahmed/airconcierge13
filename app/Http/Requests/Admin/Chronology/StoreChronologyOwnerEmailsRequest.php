<?php

namespace App\Http\Requests\Admin\Chronology;

use App\Models\Chronology;
use Illuminate\Foundation\Http\FormRequest;

class StoreChronologyOwnerEmailsRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Chronology $chronology */
        $chronology = $this->route('chronology');

        return $this->user()?->can('update', $chronology) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'owner_ids' => ['nullable', 'array'],
            'owner_ids.*' => ['integer', 'exists:owners,id'],
        ];
    }
}
