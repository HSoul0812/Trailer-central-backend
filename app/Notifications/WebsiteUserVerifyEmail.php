<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\HtmlString;

class WebsiteUserVerifyEmail extends Notification
{
    use Queueable;

    public function __construct()
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return $this->buildMailMessage($verificationUrl);
    }

    protected function verificationUrl($notifiable): string
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }

    protected function buildMailMessage($url): MailMessage
    {
        $siteUrl = config('app.site_url');

        return (new MailMessage())
            ->subject(Lang::get('TrailerTrader | Confirm your registration to TrailerTrader'))
            ->line(new HtmlString('Thank you for registering to TrailerTrader.com. We need you to confirm your email address in order to activate your account. Please click on the following link to complete the registration process.'))
            ->action('Verify Email Address', $url)
            ->line(new HtmlString("Please note that we are regularly adding features to improve your experience on the TrailerTrader platform. If you have remarks or recommendations, we’ll be happy to consider them. You can use <a href='$siteUrl/about#contact_trailertrader'>this form</a> to share them with us. "));
    }
}
