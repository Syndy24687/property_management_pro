<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('payments.create');
    }

    public function rules(): array
    {
        return [
            'lease_id'         => ['required', 'exists:leases,id'],
            'amount'           => ['required', 'numeric', 'min:0.01'],
            'payment_date'     => ['nullable', 'date'],
            'due_date'         => ['required', 'date'],
            'method'           => ['sometimes', 'in:cash,bank_transfer,credit_card,check,online'],
            'status'           => ['sometimes', 'in:pending,completed,failed,refunded'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'notes'            => ['nullable', 'string'],
        ];
    }
}
