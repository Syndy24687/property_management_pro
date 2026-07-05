<?php

namespace App\Notifications;

use App\Models\MaintenanceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MaintenanceStatusChanged extends Notification
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
            'type'       => 'maintenance_status_changed',
            'title'      => 'Maintenance Request Updated',
            'message'    => "Your request \"{$this->maintenanceRequest->title}\" status changed to: {$this->maintenanceRequest->status}",
            'request_id' => $this->maintenanceRequest->id,
            'status'     => $this->maintenanceRequest->status,
        ];
    }
}
