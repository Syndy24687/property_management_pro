<?php

namespace App\Http\Requests\MaintenanceRequest;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMaintenanceRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('maintenance.update');
    }

    public function rules(): array
    {
        return [
            'title'       => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'priority'    => ['sometimes', 'in:low,medium,high,urgent'],
            'status'      => ['sometimes', 'in:open,in_progress,resolved,closed'],
        ];
    }
}
