<?php

namespace App\Http\Requests\Property;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('properties.update');
    }

    public function rules(): array
    {
        return [
            'name'        => ['sometimes', 'string', 'max:255'],
            'address'     => ['sometimes', 'string', 'max:255'],
            'city'        => ['sometimes', 'string', 'max:100'],
            'state'       => ['sometimes', 'string', 'max:100'],
            'zip_code'    => ['sometimes', 'string', 'max:10'],
            'type'        => ['sometimes', 'in:residential,commercial,industrial,mixed_use'],
            'description' => ['nullable', 'string'],
            'status'      => ['sometimes', 'in:active,inactive,under_maintenance'],
        ];
    }
}
