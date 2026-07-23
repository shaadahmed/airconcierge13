<?php

namespace App\Http\Requests\Admin\Chronology;

use App\Models\Chronology;
use Illuminate\Foundation\Http\FormRequest;

class StoreChronologyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Chronology::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'startdate' => ['required', 'date'],
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
