<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Inventory;

class LowStockNotification extends Notification
{
    use Queueable;

    public $item;

    public function __construct(Inventory $item)
    {
        $this->item = $item;
    }

    public function via($notifiable)
    {
        return ['database'];  //   ['database']  تخزنيه في قاعدة البيانات
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('⚠️ تنبيه: كمية منخفضة في المستودع')
            ->line("الصنف: {$this->item->item_name}")
            ->line("الكمية الحالية: {$this->item->quantity}")
            ->line('يرجى إعادة تعبئة المستودع في أقرب وقت ممكن.');
    }

    public function toArray($notifiable)
    {
        return [
            'item_name' => $this->item->item_name,
            'quantity' => $this->item->quantity,
        ];
    }
}

