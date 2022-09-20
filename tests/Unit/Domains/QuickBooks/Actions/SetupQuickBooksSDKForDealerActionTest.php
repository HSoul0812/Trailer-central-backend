<?php

namespace Tests\Unit\Domains\QuickBooks\Actions;

use App\Domains\QuickBooks\Actions\GetQuickBooksSessionAction;
use App\Domains\QuickBooks\Actions\SetupQuickBooksSDKForDealerAction;
use App\Domains\QuickBooks\QuickBooksSession;
use App\Models\User\User;
use Exception;
use Mockery;
use QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2AccessToken;
use QuickBooksOnline\API\DataService\DataService;
use TCentral\QboSdk\Domain\QuickBooks\Actions\SetupQuickBooksSDKAction;
use TCentral\QboSdk\Domain\QuickBooks\Storages\DataServiceStorage;
use Tests\TestCase;

class SetupQuickBooksSDKForDealerActionTest extends TestCase
{
    /**
     * @group DMS
     * @group DMS_QUICKBOOK
     *
     * @throws Exception
     */
    public function testItCanSetupQuickBooksSDKForDealer()
    {
        $quickBooksSessionToken = $this->getActualQuickBooksSessionToken();

        $dealer = factory(User::class)->create([
            'quickbooks_session_token' => $quickBooksSessionToken,
        ]);

        $fakeQBOSession = $this->getFakeQBOSession();

        $getQuickBooksSessionAction = Mockery::mock(GetQuickBooksSessionAction::class);
        $getQuickBooksSessionAction
            ->expects('execute')
            ->with($quickBooksSessionToken)
            ->once()
            ->andReturn($fakeQBOSession);

        $action = new SetupQuickBooksSDKForDealerAction(
            $getQuickBooksSessionAction,
            resolve(SetupQuickBooksSDKAction::class)
        );

        $action->execute($dealer);

        $dataService = DataServiceStorage::get();
        $serviceContext = $dataService->getServiceContext();

        /** @var OAuth2AccessToken $oAuth2AccessToken */
        $oAuth2AccessToken = $serviceContext->requestValidator;

        $this->assertInstanceOf(DataService::class, $dataService);
        $this->assertEquals('rid', $serviceContext->realmId);
        $this->assertEquals('atk', $oAuth2AccessToken->getAccessToken());
        $this->assertEquals('rtk', $oAuth2AccessToken->getRefreshToken());

        $dealer->delete();
    }

    private function getFakeQBOSession(): QuickBooksSession
    {
        $session = new QuickBooksSession();

        $session->setAccessToken('atk');
        $session->setRealmID('rid');
        $session->setRefreshToken('rtk');
        $session->setAccessTokenExpiresAt('exp_at');

        return $session;
    }

    private function getActualQuickBooksSessionToken(): string
    {
        return 'TzozNjoiUXVpY2tib29rc1xTZXJ2aWNlXFF1aWNrYm9va3NTZXNzaW9uIjo0OntzOjQ1OiIAUXVpY2tib29rc1xTZXJ2aWNlXFF1aWNrYm9va3NTZXNzaW9uAHJlYWxtSUQiO3M6MTk6IjQ2MjA4MTYzNjUxNzk0MDEyOTAiO3M6NDk6IgBRdWlja2Jvb2tzXFNlcnZpY2VcUXVpY2tib29rc1Nlc3Npb24AYWNjZXNzVG9rZW4iO3M6OTkxOiJleUpsYm1NaU9pSkJNVEk0UTBKRExVaFRNalUySWl3aVlXeG5Jam9pWkdseUluMC4uS09KVzE3LUVqcndTY2xodWtrNlU1Zy5nb0d4VnQ0anJHcjJlRlo1UnA1U2xEOGdmOG1CNUMtX1ZHSmo2c213ZGxFTmNsd3h5SThqODdtZmd4cmY1NGJ2VnFWQVdHTURMUVZhQ0ZoXzY5dlBmRmxrbGFxYy1EX3NqVXJKUnFkdk9TQjBPRGFXeTBocjFtODF4dHQyUjZrU04yZHZaSkRxazFrVXd5Y0FCM29kNmtWSXVpcm1qaVdSLTEzbDVObVNPMVMtNW1VRkZDTWR4WW5JMjYzUkFuVXpQbDh6VXlWUWF5cmEtSzc1UHhKTE1QSmViVFpIOS1vWGZtNk9tNjMtVDZZS21aZTBxSF9RenpIeUxOMkdCdHRXbzhTSEtHTktzYW9aYzhKcVpvQUs3WDJtcGpOZWE4VjFyNVlFQl9vbmUxa2hVVHlWRDhyWjExcElOU2hBQkU4dm9PM2ZRNEZzS2NMMnZfbEhXY1cwSGRyTEhIMkZhZVV2OTB5Z1V3RUxXSmYtRF9OTzRtZ19EMXhWT2ZLeUYwdXlVaUtBNVh2aEQ1M1YwYmphVml0SHh4MnpqdWh2VU1IcUxMY2hnUVlEMV9aaU1waU02bzI5LWg1X3k2UHljSVNQNXA3THlvWmE4ODdZUmJRMTdTaTdCcEtLdjUtOUlBNDVTTmRjY1pMVExkMmNvTy1tNmdqVmsyYmdHZkZnVGU2cU9WN0dxWWwtd1FPYzI4Xy1FTW12S3VVSkJyUUR1Z1k5b2pUNlJpMWJ4ZjlzRVI1WGNZcVhBRHU4Z084SU1KQjR2NDNhR01UZnNZczFxZm56aXRDWE1RcXFzVjdvTWEzdjJvZmFGaDNwaXFGQXNMS0Fiel93d2NYd2NtUmlRZWhZdTlhODExTEhGUmhkZDJkcl9hVFdsMGFtMGJUZ2sxcV9HOEktSzRxRk10WV9pYzhfNmMwbHRqbGNzYzItNG9EQ3UyREozNU1uZ3h4bktHNElYeFVaNXFwVUluT2JaMnV0Z01xLXFzMkZpTDZnalBUSllDR0M0NVQ1aUg2SHVsTVA2ZXVpSG95LUtITnBvb3RRend6OGRHdHJlVGQ3UG1YcV9HM2NYaWNRMEVDaVJwZ3VMaEd4QjJGcWRTTVhhVFZtb2p0Qmw1Wjl4WWRRRVBHNW9IX21ReTlTWUU4cFhBVTE2Rk5rckJIU2JBaFNJa2pGY0RLVlZKOU9Cb2ZtZl9OUS4tUVRHdlBka2loZVVQWklKV2stRWFRIjtzOjUwOiIAUXVpY2tib29rc1xTZXJ2aWNlXFF1aWNrYm9va3NTZXNzaW9uAHJlZnJlc2hUb2tlbiI7czo1MDoiQUIxMTY3MjMwNDM1OUFsdkxzNUQ1dW01d1BKcmJTc1A2OElzZzc1MUZmMUFXQ2hITEwiO3M6NDc6IgBRdWlja2Jvb2tzXFNlcnZpY2VcUXVpY2tib29rc1Nlc3Npb24AZXhwaXJlc0F0IjtzOjE5OiIyMDIyLzA5LzE5IDEzOjUxOjExIjt9';
    }
}
