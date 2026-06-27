<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'address'     => $this->address,
            'city'        => $this->city,
            'state'       => $this->state,
            'zip_code'    => $this->zip_code,
            'type'        => $this->type,
            'description' => $this->description,
            'status'      => $this->status,
            'owner'       => new TenantResource($this->whenLoaded('owner')),
            'units'       => UnitResource::collection($this->whenLoaded('units')),
            'units_count' => $this->whenCounted('units'),
            'created_at'  => $this->created_at?->toISOString(),
            'updated_at'  => $this->updated_at?->toISOString(),
        ];
    }
}
