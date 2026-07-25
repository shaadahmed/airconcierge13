<?php

namespace App\Http\Requests\Admin\OwnerStatements;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OwnerStatementReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewOwnerStatements', User::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'property_id' => ['required', 'integer', 'exists:properties,id'],
            'date_range' => ['required', 'string', Rule::in([
                'this_month',
                'last_month',
                'next_month',
                'this_year',
                'last_year',
                'last_12_months',
                'specific_month',
            ])],
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
        ];
    }
}
