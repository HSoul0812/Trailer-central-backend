<?php

namespace App\Domains\QuickBooks\Actions;

use App\Domains\QuickBooks\Exceptions\InvalidSessionTokenException;
use App\Domains\QuickBooks\QuickBooksSession;
use App\Models\User\User;
use Exception;
use TCentral\QboSdk\Domain\QuickBooks\Actions\SetupQuickBooksSDKAction;

class SetupQuickBooksSDKForDealerAction
{
    /** @var GetQuickBooksSessionAction */
    private $getQuickBooksSessionAction;

    /** @var SetupQuickBooksSDKAction */
    private $setupQuickBooksSDKAction;

    public function __construct(
        GetQuickBooksSessionAction $getQuickBooksSessionAction,
        SetupQuickBooksSDKAction $setupQuickBooksSDKAction
    )
    {
        $this->getQuickBooksSessionAction = $getQuickBooksSessionAction;
        $this->setupQuickBooksSDKAction = $setupQuickBooksSDKAction;
    }

    /**
     * @throws Exception
     */
    public function execute(User $dealer)
    {
        $session = $this->getQBOSession($dealer);

        $this->setupQuickBooksSDKAction
            ->withAuthMode('oauth2')
            ->withClientId(config('quickbooks.client_id'))
            ->withClientSecret(config('quickbooks.client_secret'))
            ->withAccessTokenKey($session->getAccessToken())
            ->withRefreshTokenKey($session->getRefreshToken())
            ->withQboRealmId($session->getRealmID())
            ->withBaseUrl(config('quickbooks.base_url'))
            ->execute();
    }

    /**
     * @throws Exception
     */
    private function getQBOSession(User $dealer): QuickBooksSession
    {
        $session = $this->getQuickBooksSessionAction->execute($dealer->quickbooks_session_token);

        if ($session === null) {
            throw InvalidSessionTokenException::make($dealer->dealer_id);
        }

        return $session;
    }
}
