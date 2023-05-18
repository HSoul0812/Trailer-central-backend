<?php

namespace Tests\Integration\App\Api\ViewsAndImpressions;

use App\Domains\ViewsAndImpressions\Actions\GetTTAndAffiliateViewsAndImpressionsAction;
use App\Models\AppToken;
use Mockery;
use Mockery\MockInterface;
use Tests\Common\IntegrationTestCase;

class TTAndAffiliateControllerTest extends IntegrationTestCase
{
    public const ENDPOINT = '/api/views-and-impressions/tt-and-affiliate';

    public function testTheApiRouteWorks()
    {
        $appToken = AppToken::factory()->create();

        $this->instance(
            abstract: GetTTAndAffiliateViewsAndImpressionsAction::class,
            instance: Mockery::mock(GetTTAndAffiliateViewsAndImpressionsAction::class, function (MockInterface $mock) {
                $mock->shouldReceive('setCriteria')->once()->withAnyArgs()->andReturnSelf();
                $mock->shouldReceive('setAppToken')->once()->withAnyArgs()->andReturnSelf();
                $mock->shouldReceive('execute')->once()->withNoArgs()->andReturns();
            }),
        );

        $this
            ->getJson(self::ENDPOINT . "?app-token=$appToken->token")
            ->assertOk();
    }
}
