<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentReminderNotification extends Notification
{
    protected $appointment;

    public function __construct($appointment)
    {
        $this->appointment = $appointment;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'تذكير بموعدك الطبي',
           'message' => 'لديك موعد مع الدكتور ' . $this->appointment->doctor->name .
             ' في تاريخ ' . $this->appointment->appointment_date->format('Y-m-d H:i'),
            'appointment_id' => $this->appointment->id,
        ];
    }
}
