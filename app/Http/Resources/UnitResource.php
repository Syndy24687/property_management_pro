<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UnitResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'property_id'    => $this->property_id,
            'unit_number'    => $this->unit_number,
            'floor'          => $this->floor,
            'bedrooms'       => $this->bedrooms,
            'bathrooms'      => $this->bathrooms,
            'area_sqft'      => $this->area_sqft,
            'rent_amount'    => $this->rent_amount,
            'deposit_amount' => $this->deposit_amount,
            'status'         => $this->status,
            'property'       => new PropertyResource($this->whenLoaded('property')),
            'images' => $this->whenLoaded('images', fn() =>
                $this->images->map(fn($img) => [
                    'id'         => $img->id,
                    'url'        => $img->url,
                    'file_name'  => $img->file_name,
                    'is_primary' => $img->is_primary,
                ])
            ),
            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,
        ];
    }
}
