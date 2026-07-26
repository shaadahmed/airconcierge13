<?php

namespace App\Http\Requests\Admin\Imports;

use App\Models\ImportedEmail;
use Illuminate\Foundation\Http\FormRequest;

class StoreImportedEmailRequest extends FormRequest
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
            'source' => ['nullable', 'string', 'max:100'],
            'subject' => ['nullable', 'string', 'max:500'],
            'body' => ['nullable', 'string'],
            'from_email' => ['nullable', 'email'],
        ];
    }
}
