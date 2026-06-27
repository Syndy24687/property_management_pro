<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'start_date'     => $this->start_date?->toDateString(),
            'end_date'       => $this->end_date?->toDateString(),
            'rent_amount'    => $this->rent_amount,
            'deposit_amount' => $this->deposit_amount,
            'status'         => $this->status,
            'notes'          => $this->notes,
            'unit'           => new UnitResource($this->whenLoaded('unit')),
            'tenant'         => new TenantResource($this->whenLoaded('tenant')),
            'payments'       => PaymentResource::collection($this->whenLoaded('payments')),
            'created_at'     => $this->created_at?->toISOString(),
            'updated_at'     => $this->updated_at?->toISOString(),
        ];
    }
}
