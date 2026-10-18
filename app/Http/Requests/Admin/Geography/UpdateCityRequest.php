<?php

namespace App\Http\Requests\Admin\Geography;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCityRequest extends FormRequest
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
        $city = $this->route('city');

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('cities', 'name')
                    ->where(fn ($query) => $query->where('state_id', $state->id)->whereNull('deleted_at'))
                    ->ignore($city->id),
            ],
            'code' => ['nullable', 'string', 'max:15'],
            'zip' => ['nullable', 'integer'],
            'contact_code' => ['nullable', 'string', 'max:10'],
        ];
    }
}
