<?php

namespace App\Http\Requests\Admin\Reports;

use App\Models\Report;
use Illuminate\Foundation\Http\FormRequest;

class ReportTotRequest extends FormRequest
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
        ];
    }
}
