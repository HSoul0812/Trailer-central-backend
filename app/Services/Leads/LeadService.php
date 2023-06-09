<?php

declare(strict_types=1);

namespace App\Services\Leads;

use App\Domains\Recaptcha\Recaptcha;
use App\DTOs\Lead\TcApiResponseLead;
use App\Services\Captcha\CaptchaServiceInterface;
use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Log;
use Symfony\Component\HttpKernel\Exception\HttpException;

class LeadService implements LeadServiceInterface
{
    private const INQUIRY_SEND_ROUTE = 'inquiry/send/';

    public function __construct(public GuzzleHttpClient $httpClient, private CaptchaServiceInterface $captchaService)
    {
    }

    /**
     * @param array
     */
    public function create(array $params): TcApiResponseLead
    {
        if (!$this->captchaService->validate($params['captcha'])) {
            throw ValidationException::withMessages([
                'captcha' => Recaptcha::FAILED_CAPTCHA_MESSAGE,
            ]);
        }

        $params['lead_source'] = 'TrailerTrader';
        $params['website_id'] = config('services.trailercentral.tt_website_id');
        $params['is_from_classifieds'] = 1;
        $access_token = $this->getAccessToken($params['inventory']['inventory_id']);
        $params['inventory'][] = $params['inventory']['inventory_id'];
        $url = config('services.trailercentral.api') . self::INQUIRY_SEND_ROUTE;
        $lead = $this->handleHttpRequest('PUT', $url, ['query' => $params, 'headers' => ['access-token' => $access_token]]);

        return TcApiResponseLead::fromData($lead['data']);
    }

    private function getAccessToken(string $inventoryId): string
    {
        $inventory = DB::connection('mysql')->table('inventory')->where('inventory_id', $inventoryId)->first();
        $auth_token = DB::connection('mysql')->table('auth_token')->where('user_id', $inventory->dealer_id)->first();

        return $auth_token->access_token;
    }

    #[ArrayShape([
        'data' => [[
            'id' => 'int',
            'website_id' => 'int',
            'dealer_id' => 'int',
            'name' => 'string',
            'lead_types' => 'array',
            'email' => 'string',
            'phone' => 'string',
            'preferred_contact' => 'string',
            'address' => 'string',
            'comments' => 'string',
            'zip' => 'string',
            'note' => 'string',
            'referral' => 'string',
            'title' => 'string',
            'status' => 'string',
            'source' => 'string',
            'next_contact_date' => 'string',
            'contact_type' => 'string',
            'created_at' => 'string',
            'inventoryInterestedIn' => 'array',
        ]],
    ])]
    private function handleHttpRequest(string $method, string $url, array $options): array
    {
        try {
            $response = $this->httpClient->request($method, $url, $options);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::info('Exception was thrown while calling TrailerCentral API.');
            Log::info($e->getCode() . ': ' . $e->getMessage());

            throw new HttpException(422, $e->getMessage());
        }
    }
}
