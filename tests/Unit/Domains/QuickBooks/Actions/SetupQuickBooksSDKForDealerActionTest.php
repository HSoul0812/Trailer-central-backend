<?php

namespace Tests\Unit\Domains\QuickBooks\Actions;

use App\Domains\QuickBooks\Actions\GetQuickBooksSessionAction;
use App\Domains\QuickBooks\Actions\SetupQuickBooksSDKForDealerAction;
use App\Domains\QuickBooks\Exceptions\InvalidSessionTokenException;
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
        $quickBooksSessionToken = 'token';
        config([
            'quickbooks.client_id' => 'test_client',
            'quickbooks.client_secret' => 'test_secret',
            'quickbooks.base_url' => 'http://test.com',
        ]);

        $dealer = factory(User::class)->create([
            'quickbooks_session_token' => $quickBooksSessionToken,
        ]);

        $fakeQBOSession = new QuickBooksSession();
        $fakeQBOSession->setAccessToken('atk');
        $fakeQBOSession->setRealmID('rid');
        $fakeQBOSession->setRefreshToken('rtk');
        $fakeQBOSession->setAccessTokenExpiresAt('exp_at');

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

    /**
     * @group DMS
     * @group DMS_QUICKBOOK
     *
     * @throws Exception
     */
    public function testItThrowsExceptionWhenDealerDoesNotHaveAToken()
    {
        $dealer = factory(User::class)->create([
            'quickbooks_session_token' => '',
        ]);

        $getQuickBooksSessionAction = Mockery::mock(GetQuickBooksSessionAction::class);
        $getQuickBooksSessionAction
            ->expects('execute')
            ->with('')
            ->once()
            ->andReturn(null);

        $action = new SetupQuickBooksSDKForDealerAction(
            $getQuickBooksSessionAction,
            resolve(SetupQuickBooksSDKAction::class)
        );

        $this->expectException(InvalidSessionTokenException::class);

        $action->execute($dealer);

        $dealer->delete();
    }
}
