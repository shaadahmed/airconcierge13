<?php

namespace App\Http\Requests\Admin\Chronology;

use App\Models\Chronology;
use Illuminate\Foundation\Http\FormRequest;

class StoreChronologyOrderRequest extends FormRequest
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
            'day' => ['required', 'integer', 'min:0'],
            'time' => ['nullable', 'string', 'max:20'],
            'minute' => ['nullable', 'string', 'max:20'],
            'previousaction' => ['nullable', 'string', 'max:255'],
            'document_id' => ['nullable', 'integer', 'exists:document_uploads,id'],
            'template_id' => ['nullable', 'integer', 'exists:template,id'],
            'document_name' => ['nullable', 'string', 'max:255'],
            'template_name' => ['nullable', 'string', 'max:255'],
        ];
    }
}
