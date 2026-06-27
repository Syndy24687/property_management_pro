<?php

namespace App\Http\Requests\MaintenanceRequest;

use Illuminate\Foundation\Http\FormRequest;

class StoreMaintenanceRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('maintenance.create');
    }

    public function rules(): array
    {
        return [
            'unit_id'     => ['required', 'exists:units,id'],
            'tenant_id'   => ['sometimes', 'exists:users,id'],
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'priority'    => ['sometimes', 'in:low,medium,high,urgent'],
            'status'      => ['sometimes', 'in:open,in_progress,resolved,closed'],
        ];
    }
}
