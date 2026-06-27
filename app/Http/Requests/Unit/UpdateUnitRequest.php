<?php

namespace App\Http\Requests\Unit;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('units.update');
    }

    public function rules(): array
    {
        return [
            'unit_number'  => ['sometimes', 'string', 'max:50'],
            'bedrooms'     => ['sometimes', 'integer', 'min:0'],
            'bathrooms'    => ['sometimes', 'integer', 'min:0'],
            'area_sqft'    => ['nullable', 'numeric', 'min:0'],
            'rent_amount'  => ['sometimes', 'numeric', 'min:0'],
            'status'       => ['sometimes', 'in:available,occupied,under_maintenance'],
        ];
    }
}
