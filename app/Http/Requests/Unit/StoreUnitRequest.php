<?php

namespace App\Http\Requests\Unit;

use Illuminate\Foundation\Http\FormRequest;

class StoreUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('units.create');
    }

    public function rules(): array
    {
        return [
            'property_id'  => ['required', 'exists:properties,id'],
            'unit_number'  => ['required', 'string', 'max:50'],
            'bedrooms'     => ['sometimes', 'integer', 'min:0'],
            'bathrooms'    => ['sometimes', 'integer', 'min:0'],
            'area_sqft'    => ['nullable', 'numeric', 'min:0'],
            'rent_amount'  => ['required', 'numeric', 'min:0'],
            'status'       => ['sometimes', 'in:available,occupied,under_maintenance'],
        ];
    }
}
