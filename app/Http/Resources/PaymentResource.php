<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'lease_id'         => $this->lease_id,
            'invoice_id'       => $this->invoice_id,
            'amount'           => $this->amount,
            'payment_date'     => $this->payment_date?->format('Y-m-d'),
            'due_date'         => $this->due_date?->format('Y-m-d'),
            'method'           => $this->method,
            'status'           => $this->status,
            'reference_number' => $this->reference_number,
            'transaction_id'   => $this->transaction_id,
            'notes'            => $this->notes,
            'lease'            => new LeaseResource($this->whenLoaded('lease')),
            'invoice'          => new InvoiceResource($this->whenLoaded('invoice')),
            'received_by'      => $this->whenLoaded('receivedBy', fn() => [
                'id'   => $this->receivedBy->id,
                'name' => $this->receivedBy->name,
            ]),
            'created_at'       => $this->created_at,
        ];
    }
}
