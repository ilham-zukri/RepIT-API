<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class SendNotification extends Notification
{
    use Queueable;
    private $title;
    private $body;
    private $type;

    public function __construct($title, $body, $type){
        $this->title = $title;
        $this->body = $body;
        $this->type = $type;
    }
    /**
     * Create a new notification instance.
     */
    public function via($notifiable)
    {
        return [FcmChannel::class];
    }

    public function toFcm($notifiable): FcmMessage
    {
        return FcmMessage::create()
            ->setNotification(
                FcmNotification::create()
                    ->setTitle($this->title)
                    ->setBody($this->body)
            )->setData(['type' => $this->type]);
    }
}
