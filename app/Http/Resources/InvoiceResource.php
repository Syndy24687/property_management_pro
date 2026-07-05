<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'lease_id'        => $this->lease_id,
            'invoice_number'  => $this->invoice_number,
            'issue_date'      => $this->issue_date?->format('Y-m-d'),
            'due_date'        => $this->due_date?->format('Y-m-d'),
            'subtotal'        => $this->subtotal,
            'tax_amount'      => $this->tax_amount,
            'total_amount'    => $this->total_amount,
            'amount_paid'     => $this->amount_paid,
            'balance_due'     => $this->balance_due,
            'status'          => $this->status,
            'notes'           => $this->notes,
            'items'           => InvoiceItemResource::collection($this->whenLoaded('items')),
            'payments'        => PaymentResource::collection($this->whenLoaded('payments')),
            'lease'           => new LeaseResource($this->whenLoaded('lease')),
            'created_at'      => $this->created_at,
        ];
    }
}
