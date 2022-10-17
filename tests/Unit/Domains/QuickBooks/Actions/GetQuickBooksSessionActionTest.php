<?php

namespace Tests\Unit\Domains\QuickBooks\Actions;

use App\Domains\CRM\Services\CRMHttpClient;
use App\Domains\QuickBooks\Actions\GetQuickBooksSessionAction;
use App\Domains\QuickBooks\QuickBooksSession;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use Mockery;
use Tests\TestCase;

class GetQuickBooksSessionActionTest extends TestCase
{
    /**
     * @group DMS
     * @group DMS_QUICKBOOK
     *
     * @return void
     */
    public function testItReturnsNullForBadRequest()
    {
        $token = '';

        $response = new Response(200, [], json_encode([]));

        $httpClient = Mockery::mock(CRMHttpClient::class);
        $httpClient
            ->expects('post')
            ->withArgs([
                GetQuickBooksSessionAction::DECODE_SESSION_TOKEN_ENDPOINT,
                [
                    RequestOptions::FORM_PARAMS => [
                        'token' => $token,
                    ],
                ]
            ])
            ->once()
            ->andReturn($response);

        $action = new GetQuickBooksSessionAction($httpClient);

        $session = $action->execute($token);

        $this->assertNull($session);
    }

    /**
     * @group DMS
     * @group DMS_QUICKBOOK
     *
     * @return void
     */
    public function testItReturnsProperSessionObject()
    {
        $token = 'token';

        $response = new Response(200, [], json_encode([
            'session' => [
                'access_token' => 'atk',
                'refresh_token' => 'rtk',
                'realm_id' => 'rid',
                'expires_at' => 'exp_at',
            ],
        ]));

        $httpClient = Mockery::mock(CRMHttpClient::class);
        $httpClient
            ->expects('post')
            ->withArgs([
                GetQuickBooksSessionAction::DECODE_SESSION_TOKEN_ENDPOINT,
                [
                    RequestOptions::FORM_PARAMS => [
                        'token' => $token,
                    ],
                ]
            ])
            ->once()
            ->andReturn($response);

        $action = new GetQuickBooksSessionAction($httpClient);

        $session = $action->execute($token);

        $this->assertInstanceOf(QuickBooksSession::class, $session);
        $this->assertEquals('atk', $session->getAccessToken());
        $this->assertEquals('rtk', $session->getRefreshToken());
        $this->assertEquals('rid', $session->getRealmID());
        $this->assertEquals('exp_at', $session->getAccessTokenExpiresAt());
    }
}
