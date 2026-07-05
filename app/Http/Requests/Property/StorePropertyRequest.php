<?php

namespace App\Http\Requests\Property;

use Illuminate\Foundation\Http\FormRequest;

class StorePropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('properties.create');
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'address'     => ['required', 'string', 'max:255'],
            'city'        => ['required', 'string', 'max:100'],
            'state'       => ['required', 'string', 'max:100'],
            'zip_code'    => ['required', 'string', 'max:10'],
            'type'        => ['required', 'in:residential,commercial,industrial,mixed_use'],
            'description' => ['nullable', 'string'],
            'status'      => ['sometimes', 'in:active,inactive,under_maintenance'],
            'owner_id'    => ['sometimes', 'exists:users,id'],
            'company_id'  => ['sometimes', 'exists:companies,id'],
            'latitude'    => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'   => ['nullable', 'numeric', 'between:-180,180'],
            'year_built'  => ['nullable', 'integer', 'min:1800', 'max:' . (date('Y') + 5)],
            'images'      => ['sometimes', 'array', 'max:10'],
            'images.*'    => ['image', 'max:5120'],
        ];
    }
}
