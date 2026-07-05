<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class MaintenanceRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'unit_id'         => $this->unit_id,
            'tenant_id'       => $this->tenant_id,
            'category_id'     => $this->category_id,
            'assigned_to'     => $this->assigned_to,
            'title'           => $this->title,
            'description'     => $this->description,
            'priority'        => $this->priority,
            'status'          => $this->status,
            'estimated_cost'  => $this->estimated_cost,
            'actual_cost'     => $this->actual_cost,
            'scheduled_date'  => $this->scheduled_date?->format('Y-m-d H:i'),
            'resolved_at'     => $this->resolved_at?->format('Y-m-d H:i'),
            'unit'            => new UnitResource($this->whenLoaded('unit')),
            'tenant'          => $this->whenLoaded('tenant', fn() => [
                'id'   => $this->tenant->id,
                'name' => $this->tenant->name,
            ]),
            'category'        => $this->whenLoaded('category', fn() => [
                'id'   => $this->category->id,
                'name' => $this->category->name,
                'icon' => $this->category->icon,
            ]),
            'assignee'        => $this->whenLoaded('assignee', fn() => [
                'id'   => $this->assignee->id,
                'name' => $this->assignee->name,
            ]),
            'comments'        => MaintenanceCommentResource::collection($this->whenLoaded('comments')),
            'attachments'     => $this->whenLoaded('documents', fn() =>
                $this->documents->map(fn($d) => [
                    'id'        => $d->id,
                    'title'     => $d->title,
                    'url'       => Storage::url($d->file_path),
                    'mime_type' => $d->mime_type,
                ])
            ),
            'created_at'      => $this->created_at,
            'updated_at'      => $this->updated_at,
        ];
    }
}
