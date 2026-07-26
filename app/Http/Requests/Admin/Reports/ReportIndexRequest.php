<?php

namespace App\Http\Requests\Admin\Reports;

use App\Models\Report;
use Illuminate\Foundation\Http\FormRequest;

class ReportIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Report::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'month' => ['nullable', 'integer', 'between:1,12'],
            'region_id' => ['nullable', 'integer', 'exists:regions,id'],
            'property_id' => ['nullable', 'integer', 'exists:properties,id'],
        ];
    }
}
