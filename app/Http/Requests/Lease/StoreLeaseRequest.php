<?php

namespace App\Http\Requests\Lease;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('leases.create');
    }

    public function rules(): array
    {
        return [
            'unit_id'        => ['required', 'exists:units,id'],
            'tenant_id'      => ['required', 'exists:users,id'],
            'start_date'     => ['required', 'date', 'after_or_equal:today'],
            'end_date'       => ['required', 'date', 'after:start_date'],
            'rent_amount'    => ['required', 'numeric', 'min:0'],
            'deposit_amount' => ['sometimes', 'numeric', 'min:0'],
            'status'         => ['sometimes', 'in:pending,active,expired,terminated'],
            'notes'          => ['nullable', 'string'],
        ];
    }
}
