<?php

namespace App\Domains\QuickBooks\Actions;

use App\Domains\CRM\Services\CRMHttpClient;
use App\Domains\QuickBooks\QuickBooksSession;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Arr;

class GetQuickBooksSessionAction
{
    const DECODE_SESSION_TOKEN_ENDPOINT = '/quickbooks/sessions/decode';

    /** @var CRMHttpClient */
    private $client;

    public function __construct(CRMHttpClient $client)
    {
        $this->client = $client;
    }

    /**
     * @param string $quickbooksSessionToken
     * @throws ClientException
     * @return QuickBooksSession|null
     */
    public function execute(string $quickbooksSessionToken): ?QuickBooksSession
    {
        // Make a call to CRM to decode the stored token
        $response = $this->client->post(self::DECODE_SESSION_TOKEN_ENDPOINT, [
            RequestOptions::FORM_PARAMS => [
                'token' => $quickbooksSessionToken,
            ],
        ]);

        $session = Arr::get(json_decode($response->getBody(), true), 'session');

        if ($session === null) {
            return null;
        }

        // Compose a new QuickBooksSession object to use in this project
        return QuickBooksSession::make(
            Arr::get($session, 'realm_id'),
            Arr::get($session, 'access_token'),
            Arr::get($session, 'refresh_token'),
            Arr::get($session, 'expires_at')
        );
    }
}
