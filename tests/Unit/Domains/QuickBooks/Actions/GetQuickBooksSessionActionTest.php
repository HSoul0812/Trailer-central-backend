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
        $token = 'TzozNjoiUXVpY2tib29rc1xTZXJ2aWNlXFF1aWNrYm9va3NTZXNzaW9uIjo0OntzOjQ1OiIAUXVpY2tib29rc1xTZXJ2aWNlXFF1aWNrYm9va3NTZXNzaW9uAHJlYWxtSUQiO3M6MTk6IjQ2MjA4MTYzNjUxNzk0MDEyOTAiO3M6NDk6IgBRdWlja2Jvb2tzXFNlcnZpY2VcUXVpY2tib29rc1Nlc3Npb24AYWNjZXNzVG9rZW4iO3M6OTkxOiJleUpsYm1NaU9pSkJNVEk0UTBKRExVaFRNalUySWl3aVlXeG5Jam9pWkdseUluMC4uS09KVzE3LUVqcndTY2xodWtrNlU1Zy5nb0d4VnQ0anJHcjJlRlo1UnA1U2xEOGdmOG1CNUMtX1ZHSmo2c213ZGxFTmNsd3h5SThqODdtZmd4cmY1NGJ2VnFWQVdHTURMUVZhQ0ZoXzY5dlBmRmxrbGFxYy1EX3NqVXJKUnFkdk9TQjBPRGFXeTBocjFtODF4dHQyUjZrU04yZHZaSkRxazFrVXd5Y0FCM29kNmtWSXVpcm1qaVdSLTEzbDVObVNPMVMtNW1VRkZDTWR4WW5JMjYzUkFuVXpQbDh6VXlWUWF5cmEtSzc1UHhKTE1QSmViVFpIOS1vWGZtNk9tNjMtVDZZS21aZTBxSF9RenpIeUxOMkdCdHRXbzhTSEtHTktzYW9aYzhKcVpvQUs3WDJtcGpOZWE4VjFyNVlFQl9vbmUxa2hVVHlWRDhyWjExcElOU2hBQkU4dm9PM2ZRNEZzS2NMMnZfbEhXY1cwSGRyTEhIMkZhZVV2OTB5Z1V3RUxXSmYtRF9OTzRtZ19EMXhWT2ZLeUYwdXlVaUtBNVh2aEQ1M1YwYmphVml0SHh4MnpqdWh2VU1IcUxMY2hnUVlEMV9aaU1waU02bzI5LWg1X3k2UHljSVNQNXA3THlvWmE4ODdZUmJRMTdTaTdCcEtLdjUtOUlBNDVTTmRjY1pMVExkMmNvTy1tNmdqVmsyYmdHZkZnVGU2cU9WN0dxWWwtd1FPYzI4Xy1FTW12S3VVSkJyUUR1Z1k5b2pUNlJpMWJ4ZjlzRVI1WGNZcVhBRHU4Z084SU1KQjR2NDNhR01UZnNZczFxZm56aXRDWE1RcXFzVjdvTWEzdjJvZmFGaDNwaXFGQXNMS0Fiel93d2NYd2NtUmlRZWhZdTlhODExTEhGUmhkZDJkcl9hVFdsMGFtMGJUZ2sxcV9HOEktSzRxRk10WV9pYzhfNmMwbHRqbGNzYzItNG9EQ3UyREozNU1uZ3h4bktHNElYeFVaNXFwVUluT2JaMnV0Z01xLXFzMkZpTDZnalBUSllDR0M0NVQ1aUg2SHVsTVA2ZXVpSG95LUtITnBvb3RRend6OGRHdHJlVGQ3UG1YcV9HM2NYaWNRMEVDaVJwZ3VMaEd4QjJGcWRTTVhhVFZtb2p0Qmw1Wjl4WWRRRVBHNW9IX21ReTlTWUU4cFhBVTE2Rk5rckJIU2JBaFNJa2pGY0RLVlZKOU9Cb2ZtZl9OUS4tUVRHdlBka2loZVVQWklKV2stRWFRIjtzOjUwOiIAUXVpY2tib29rc1xTZXJ2aWNlXFF1aWNrYm9va3NTZXNzaW9uAHJlZnJlc2hUb2tlbiI7czo1MDoiQUIxMTY3MjMwNDM1OUFsdkxzNUQ1dW01d1BKcmJTc1A2OElzZzc1MUZmMUFXQ2hITEwiO3M6NDc6IgBRdWlja2Jvb2tzXFNlcnZpY2VcUXVpY2tib29rc1Nlc3Npb24AZXhwaXJlc0F0IjtzOjE5OiIyMDIyLzA5LzE5IDEzOjUxOjExIjt9';

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
