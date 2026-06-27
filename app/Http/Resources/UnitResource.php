<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UnitResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'unit_number'  => $this->unit_number,
            'bedrooms'     => $this->bedrooms,
            'bathrooms'    => $this->bathrooms,
            'area_sqft'    => $this->area_sqft,
            'rent_amount'  => $this->rent_amount,
            'status'       => $this->status,
            'property'     => new PropertyResource($this->whenLoaded('property')),
            'leases'       => LeaseResource::collection($this->whenLoaded('leases')),
            'created_at'   => $this->created_at?->toISOString(),
            'updated_at'   => $this->updated_at?->toISOString(),
        ];
    }
}
