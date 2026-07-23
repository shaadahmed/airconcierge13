<?php

namespace App\Http\Requests\Admin\PropertyPayments;

use App\Models\PropertyPayment;
use Illuminate\Foundation\Http\FormRequest;

class StorePropertyPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', PropertyPayment::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'property_id' => ['required', 'integer', 'exists:properties,id'],
            'property_payment_type_id' => ['nullable', 'integer', 'exists:property_payment_types,id'],
            'amount' => ['required', 'numeric'],
            'payment_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
