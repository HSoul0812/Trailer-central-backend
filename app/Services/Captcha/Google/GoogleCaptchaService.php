<?php

namespace App\Services\Captcha\Google;

use App\Models\RecaptchaLog;
use App\Services\Captcha\CaptchaServiceInterface;
use Http;

/**
 * @see https://developers.google.com/recaptcha/docs/v3
 * @see https://developers.google.com/recaptcha/docs/verify
 */
class GoogleCaptchaService implements CaptchaServiceInterface
{
    public const API_ENDPOINT = 'https://www.google.com/recaptcha/api/siteverify';

    private float $humanThreshold;

    private string $captchaSecretKey;

    public function __construct()
    {
        $this->captchaSecretKey = config('services.google.captcha.key');
        $this->humanThreshold = config('services.google.captcha.human_threshold');
    }

    public function validate(string $response): bool
    {
        $ip = request()->ip();

        $response = Http::asForm()
            ->post(self::API_ENDPOINT, [
                'secret' => $this->captchaSecretKey,
                'response' => $response,
                'remoteip' => $ip,
            ])
            ->json();

        info(json_encode($response));

        $score = $response['score'];

        RecaptchaLog::create([
            'score' => $score,
            'user_agent' => request()->userAgent(),
            'ip' => $ip,
            'action' => $response['action'],
            'path' => request()->path(),
        ]);

        return $response['success'] && $score > $this->humanThreshold;
    }
}
