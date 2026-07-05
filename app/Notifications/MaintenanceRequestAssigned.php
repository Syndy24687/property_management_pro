<?php

namespace App\Notifications;

use App\Models\MaintenanceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MaintenanceRequestAssigned extends Notification
{
    use Queueable;

    public function __construct(
        protected MaintenanceRequest $maintenanceRequest
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'    => 'maintenance_assigned',
            'title'   => 'Maintenance Request Assigned',
            'message' => "You have been assigned to: {$this->maintenanceRequest->title}",
            'request_id' => $this->maintenanceRequest->id,
            'unit'    => $this->maintenanceRequest->unit?->unit_number,
            'priority' => $this->maintenanceRequest->priority,
        ];
    }
}
