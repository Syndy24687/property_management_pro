<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaintenanceCommentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'comment'     => $this->comment,
            'is_internal' => $this->is_internal,
            'user'        => [
                'id'   => $this->user->id,
                'name' => $this->user->name,
            ],
            'created_at'  => $this->created_at,
        ];
    }
}
