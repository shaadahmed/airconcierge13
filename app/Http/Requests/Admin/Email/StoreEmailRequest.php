<?php

namespace App\Http\Requests\Admin\Email;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class StoreEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        $role = $this->user()?->role;

        return match ($role) {
            UserRole::SuperAdmin, UserRole::Admin, UserRole::Manager => true,
            default => false,
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'owner_id' => ['required', 'integer', 'exists:owners,id'],
            'property_id' => ['nullable', 'integer', 'exists:properties,id'],
            'document_id' => ['nullable', 'integer', 'exists:document_uploads,id'],
            'template_id' => ['nullable', 'integer', 'exists:template,id'],
            'subject' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
        ];
    }
}
