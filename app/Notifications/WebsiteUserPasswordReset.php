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

    public static function setResetUrl($resetUrl) {
        self::$resetUrl = $resetUrl;
    }

    public function __construct($token)
    {
        $this->token = $token;
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
        return (new MailMessage)
            ->subject(Lang::get('Reset Password Notification'))
            ->line(Lang::get('You are receiving this email because we received a password reset request for your account.'))
            ->action(Lang::get('Reset Password'), $url)
            ->line(Lang::get('This password reset link will expire in :count minutes.', ['count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire')]))
            ->line(Lang::get('If you did not request a password reset, no further action is required.'));
    }

    protected function resetUrl($notifiable)
    {
        $resetPasswordUrl = self::$resetUrl ?? config('auth.reset_password_url');
        $token = $this->token;
        $email = $notifiable->getEmailForPasswordReset();
        if($resetPasswordUrl) {
            return "$resetPasswordUrl?token=$token&email=$email";
        } else {
            return url(route('password.reset', [
                'token' => $this->token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));
        }
    }
}
