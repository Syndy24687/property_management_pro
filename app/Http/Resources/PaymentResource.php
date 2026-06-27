<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'amount'           => $this->amount,
            'payment_date'     => $this->payment_date?->toDateString(),
            'due_date'         => $this->due_date?->toDateString(),
            'method'           => $this->method,
            'status'           => $this->status,
            'reference_number' => $this->reference_number,
            'notes'            => $this->notes,
            'lease'            => new LeaseResource($this->whenLoaded('lease')),
            'created_at'       => $this->created_at?->toISOString(),
            'updated_at'       => $this->updated_at?->toISOString(),
        ];
    }
}
