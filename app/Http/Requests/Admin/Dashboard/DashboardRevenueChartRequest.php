<?php

namespace App\Http\Requests\Admin\Dashboard;

use App\Models\Dashboard;
use Illuminate\Foundation\Http\FormRequest;

class DashboardRevenueChartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Dashboard::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'months' => ['nullable', 'integer', 'between:1,36'],
        ];
    }
}
