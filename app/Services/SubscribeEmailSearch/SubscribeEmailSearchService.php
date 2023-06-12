<?php

declare(strict_types=1);

namespace App\Services\SubscribeEmailSearch;

use App\Domains\Recaptcha\Recaptcha;
use App\DTOs\SubscribeEmailSearch\SubscribeEmailSearchDTO;
use App\Mail\SubscribeEmailSearch\SubscribeEmailSearchMail;
use App\Models\SubscribeEmailSearch\SubscribeEmailSearch;
use App\Repositories\SubscribeEmailSearch\SubscribeEmailSearchRepositoryInterface;
use App\Services\Captcha\CaptchaServiceInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class SubscribeEmailSearchService implements SubscribeEmailSearchServiceInterface
{
    public function __construct(
         private CaptchaServiceInterface $captchaService,
         private SubscribeEmailSearchRepositoryInterface $subscribeEmailSearchRepository
     ) {
    }

    public function send(array $params): SubscribeEmailSearch
    {
        if (!$this->captchaService->validate($params['captcha'])) {
            throw ValidationException::withMessages([
                'captcha' => Recaptcha::FAILED_CAPTCHA_MESSAGE,
            ]);
        }
        $email = Mail::to([$params['email']]);

        $subscribeEmailSearchDTO = $this->fill($params);

        $subscribeEmailSearch = $this->subscribeEmailSearchRepository->create($params);

        $email->send(new SubscribeEmailSearchMail($subscribeEmailSearchDTO));

        $subscribeEmailSearch->subscribe_email_sent = Carbon::now()->setTimezone('UTC')->toDateTimeString();

        $subscribeEmailSearch->save();

        return $subscribeEmailSearch;
    }

    public function fill(array $params): SubscribeEmailSearchDTO
    {
        $params['subject'] = 'TrailerTrader.com | Your saved search on ' . Carbon::now()->format('Y-m-d H:i:s');

        $subscribeEmailSearchDTO = SubscribeEmailSearchDTO::fromData($params);

        return $subscribeEmailSearchDTO;
    }
}
