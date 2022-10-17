<?php

namespace Tests\Unit\Domains\QuickBooks\Actions;

use App\Domains\CRM\Services\CRMHttpClient;
use App\Domains\QuickBooks\Actions\StoreQuickBooksSessionAction;
use App\Domains\QuickBooks\QuickBooksSession;
use App\Models\User\User;
use Exception;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

class StoreQuickBooksSessionActionTest extends TestCase
{
    /**
     * @group DMS
     * @group DMS_QUICKBOOK
     *
     * @return void
     * @throws Exception
     */
    public function testItCanStoreQuickBooksSessionToDealerRecord()
    {
        $dealer = factory(User::class)->create();

        $quickbooksSession = new QuickBooksSession();

        $quickbooksSession->setAccessToken('atk');
        $quickbooksSession->setRefreshToken('rtk');
        $quickbooksSession->setRealmID('rid');
        $quickbooksSession->setAccessTokenExpiresAt('exp_at');

        $fakeToken = Str::random();
        $response = new Response(200, [], json_encode([
            'token' => $fakeToken,
        ]));

        $httpClient = Mockery::mock(CRMHttpClient::class);
        $httpClient
            ->expects('post')
            ->withArgs([
                StoreQuickBooksSessionAction::ENCODE_SESSION_TOKEN_ENDPOINT,
                [
                    RequestOptions::FORM_PARAMS => $quickbooksSession->toArray()
                ]
            ])
            ->once()
            ->andReturn($response);

        $action = new StoreQuickBooksSessionAction($httpClient);

        $user = $action->execute($dealer, $quickbooksSession);

        $this->assertEquals($fakeToken, $user->quickbooks_session_token);

        $dealer->delete();
    }
}
