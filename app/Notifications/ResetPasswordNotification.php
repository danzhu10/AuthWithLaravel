<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public $url;

    public function __construct(string $url)
    {
        $this->url = $url;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('Lupa Password ?')
                    ->action('Klik disini untuk reset password', $this->url)
                    ->line('Terima Kasih Sudah menggunakan Aplikasi Tempo!');
    }

    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}