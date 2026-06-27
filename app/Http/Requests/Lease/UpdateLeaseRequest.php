<?php

namespace App\Http\Requests\Lease;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLeaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('leases.update');
    }

    public function rules(): array
    {
        return [
            'start_date'     => ['sometimes', 'date'],
            'end_date'       => ['sometimes', 'date', 'after:start_date'],
            'rent_amount'    => ['sometimes', 'numeric', 'min:0'],
            'deposit_amount' => ['sometimes', 'numeric', 'min:0'],
            'status'         => ['sometimes', 'in:pending,active,expired,terminated'],
            'notes'          => ['nullable', 'string'],
        ];
    }
}
