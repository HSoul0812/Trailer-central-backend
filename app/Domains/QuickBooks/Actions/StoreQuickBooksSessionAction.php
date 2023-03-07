<?php

namespace App\Domains\QuickBooks\Actions;

use App\Domains\CRM\Services\CRMHttpClient;
use App\Domains\QuickBooks\QuickBooksSession;
use App\Models\User\User;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Arr;

class StoreQuickBooksSessionAction
{
    const ENCODE_SESSION_TOKEN_ENDPOINT = '/quickbooks/sessions/encode';

    /** @var CRMHttpClient */
    private $client;

    public function __construct(CRMHttpClient $client)
    {
        $this->client = $client;
    }

    /**
     * @param User $user
     * @param QuickBooksSession $quickbooksSession
     * @throws ClientException
     * @return User
     */
    public function execute(User $user, QuickBooksSession $quickbooksSession): User
    {
        // Make a call to CRM to decode the stored token
        $response = $this->client->post(self::ENCODE_SESSION_TOKEN_ENDPOINT, [
            RequestOptions::FORM_PARAMS => $quickbooksSession->toArray(),
        ]);

        $token = Arr::get(json_decode($response->getBody(), true), 'token');

        $user->quickbooks_session_token = $token;
        $user->save();

        return $user;
    }
}
