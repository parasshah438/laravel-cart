<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(protected string $url) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Admin Panel — Password Reset Request')
            ->greeting('Hello, ' . $notifiable->name . '!')
            ->line('You are receiving this email because a password reset was requested for your admin account.')
            ->action('Reset Password', $this->url)
            ->line('This link will expire in **60 minutes**.')
            ->line('If you did not request a password reset, no further action is required.');
    }
}
