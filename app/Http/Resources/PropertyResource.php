<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'owner_id'    => $this->owner_id,
            'company_id'  => $this->company_id,
            'name'        => $this->name,
            'address'     => $this->address,
            'city'        => $this->city,
            'state'       => $this->state,
            'zip_code'    => $this->zip_code,
            'type'        => $this->type,
            'description' => $this->description,
            'status'      => $this->status,
            'latitude'    => $this->latitude,
            'longitude'   => $this->longitude,
            'year_built'  => $this->year_built,
            'owner'       => $this->whenLoaded('owner', fn() => [
                'id'   => $this->owner->id,
                'name' => $this->owner->name,
            ]),
            'images' => $this->whenLoaded('images', fn() =>
                $this->images->map(fn($img) => [
                    'id'         => $img->id,
                    'url'        => $img->url,
                    'file_name'  => $img->file_name,
                    'is_primary' => $img->is_primary,
                ])
            ),
            'units_count' => $this->whenLoaded('units', fn() => $this->units->count()),
            'managers'    => $this->whenLoaded('propertyManagers', fn() =>
                $this->propertyManagers->map(fn($pm) => [
                    'id'         => $pm->user->id ?? null,
                    'name'       => $pm->user->name ?? null,
                    'is_primary' => $pm->is_primary,
                ])
            ),
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
        ];
    }
}
