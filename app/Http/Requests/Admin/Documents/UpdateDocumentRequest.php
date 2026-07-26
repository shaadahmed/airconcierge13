<?php

namespace App\Http\Requests\Admin\Documents;

use App\Models\DocumentUpload;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $document = $this->route('document');

        return $document instanceof DocumentUpload
            ? ($this->user()?->can('update', $document) ?? false)
            : false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'ownerspecific' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'integer'],
            'signid' => ['nullable', 'string', 'max:255'],
            'zohoactionid' => ['nullable', 'string', 'max:255'],
            'roletitle' => ['nullable', 'string', 'max:255'],
            'region_ids' => ['nullable', 'array'],
            'subregion_ids' => ['nullable', 'array'],
            'owner_ids' => ['nullable', 'array'],
        ];
    }
}
