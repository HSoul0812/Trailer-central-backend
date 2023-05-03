<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class WebsiteUserPasswordReset extends Notification
{
    use Queueable;

    public string $token;
    private static ?string $resetUrl;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public static function setResetUrl($resetUrl)
    {
        self::$resetUrl = $resetUrl;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = $this->resetUrl($notifiable);

        return $this->buildMailMessage($verificationUrl);
    }

    protected function buildMailMessage($url)
    {
        return (new MailMessage())
            ->subject(Lang::get('TrailerTrader | Password Recovery'))
            ->line(Lang::get('We have received a request to reset your password on TrailerTrader.com.'))
            ->line(Lang::get('If you have not initiated this process, please erase this email.
            You can continue to access TrailerTrader.com with the email and password
            that you have initially set.'))
            ->line(Lang::get('If you have forgotten your password,click on the following link to create a new password: '))
            ->action(Lang::get('Reset Password'), $url)
            ->line(Lang::get('This password reset link will expire in :count minutes.', ['count' => config('auth.passwords.' . config('auth.defaults.passwords') . '.expire')]));
    }

    protected function resetUrl($notifiable)
    {
        $resetPasswordUrl = self::$resetUrl ?? config('auth.reset_password_url');
        $token = $this->token;
        $email = $notifiable->getEmailForPasswordReset();
        if ($resetPasswordUrl) {
            return "$resetPasswordUrl?token=$token&email=$email";
        } else {
            return url(route('password.reset', [
                'token' => $this->token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));
        }
    }
}
