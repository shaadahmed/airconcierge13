<?php

namespace App\Http\Requests\Admin\Chronology;

use App\Models\Chronology;
use Illuminate\Foundation\Http\FormRequest;

class UpdateChronologyRequest extends FormRequest
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
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'startdate' => ['sometimes', 'required', 'date'],
            'chronologyoption' => ['nullable', 'integer', 'in:0,1,2'],
            'allregion' => ['nullable', 'integer'],
            'allsubregion' => ['nullable', 'integer'],
            'region_ids' => ['nullable', 'array'],
            'region_ids.*' => ['integer', 'exists:regions,id'],
            'subregion_ids' => ['nullable', 'array'],
            'subregion_ids.*' => ['integer', 'exists:subregions,id'],
        ];
    }
}
