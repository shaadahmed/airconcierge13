<?php

namespace App\Http\Requests\Admin\Imports;

use App\Models\ImportedEmail;
use Illuminate\Foundation\Http\FormRequest;

class ImportOwnersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', ImportedEmail::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'rows' => ['required', 'array'],
            'rows.*.owner_email' => ['nullable', 'email'],
            'rows.*.full_name' => ['nullable', 'string'],
            'rows.*.first_name' => ['nullable', 'string'],
            'rows.*.last_name' => ['nullable', 'string'],
            'rows.*.owner_phone' => ['nullable', 'string'],
            'rows.*.region_id' => ['nullable', 'integer'],
        ];
    }
}
