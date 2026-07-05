<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->id,
            'unit_id'               => $this->unit_id,
            'tenant_id'             => $this->tenant_id,
            'start_date'            => $this->start_date?->format('Y-m-d'),
            'end_date'              => $this->end_date?->format('Y-m-d'),
            'rent_amount'           => $this->rent_amount,
            'deposit_amount'        => $this->deposit_amount,
            'payment_frequency'     => $this->payment_frequency,
            'payment_day_of_month'  => $this->payment_day_of_month,
            'late_fee_amount'       => $this->late_fee_amount,
            'grace_period_days'     => $this->grace_period_days,
            'auto_renew'            => $this->auto_renew,
            'status'                => $this->status,
            'notes'                 => $this->notes,
            'unit'                  => new UnitResource($this->whenLoaded('unit')),
            'tenant'                => $this->whenLoaded('tenant', fn() => [
                'id'    => $this->tenant->id,
                'name'  => $this->tenant->name,
                'email' => $this->tenant->email,
                'phone' => $this->tenant->phone,
            ]),
            'co_tenants'            => $this->whenLoaded('leaseTenants', fn() =>
                $this->leaseTenants->map(fn($lt) => [
                    'id'         => $lt->tenant->id ?? null,
                    'name'       => $lt->tenant->name ?? null,
                    'is_primary' => $lt->is_primary,
                ])
            ),
            'invoices_count'        => $this->whenLoaded('invoices', fn() => $this->invoices->count()),
            'created_at'            => $this->created_at,
            'updated_at'            => $this->updated_at,
        ];
    }
}
