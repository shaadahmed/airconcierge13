<?php

namespace App\Http\Requests\Admin\Geography;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('country')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $state = $this->route('state');

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('cities', 'name')->where(
                    fn ($query) => $query->where('state_id', $state->id)->whereNull('deleted_at')
                ),
            ],
            'code' => ['nullable', 'string', 'max:15'],
            'zip' => ['nullable', 'integer'],
            'contact_code' => ['nullable', 'string', 'max:10'],
        ];
    }
}
