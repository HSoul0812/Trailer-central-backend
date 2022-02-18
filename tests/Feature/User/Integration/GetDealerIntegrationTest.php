<?php

namespace Tests\Feature\User\Integration;

use App\Models\Inventory\Inventory;

class GetDealerIntegrationTest extends DealerIntegrationTest
{
    protected const VERB = 'GET';
    protected const ENDPOINT = '/api/user/integrations/{id}';

    protected const RACINGJUNK_CODE = 'racingjunk';
    protected const KSL_CODE = 'ksl';
    protected const RVTRADER_CODE = 'rvtraderonline';

    public function testShouldPreventAccessingWithoutAuthentication(): void
    {
        $this->json(
            static::VERB,
            str_replace('{id}', $this->faker->numberBetween(3, 3000), static::ENDPOINT)
        )->assertStatus(403);
    }

    public function testShouldNotSeeNonExistentIntegration(): void
    {
        ['token' => $token, 'dealer' => $dealer] = $this->createDealerIntegration(self::RACINGJUNK_CODE);

        $response = $this->withHeaders(['access-token' => $token->access_token])
            ->json(
                static::VERB,
                str_replace('{id}', $this->faker->numberBetween(5000, 6000), static::ENDPOINT)
            );

        $response->assertStatus(422);

        $json = json_decode($response->content(), true);

        self::assertArrayHasKey('message', $json);
        self::assertArrayHasKey('integration_id', $json['errors']);
        self::assertSame('The selected integration id is invalid.', $json['errors']['integration_id'][0]);

        $this->tearDownSeed($dealer->dealer_id);
    }

    public function testShouldNotSeeInactiveIntegration(): void
    {
        [
            'token' => $token,
            'dealer' => $dealer,
            'dealer_integration' => $dealerIntegration
        ] = $this->createDealerIntegration(self::RACINGJUNK_CODE, ['active' => 0]);

        $response = $this->withHeaders(['access-token' => $token->access_token])
            ->json(
                static::VERB,
                str_replace('{id}', $dealerIntegration->integration_id, static::ENDPOINT)
            );

        $response->assertStatus(404);

        $json = json_decode($response->content(), true);

        self::assertArrayHasKey('message', $json);
        self::assertSame('No query results for model [App\Models\User\Integration\DealerIntegration].', $json['message']);

        $this->tearDownSeed($dealer->dealer_id);
    }

    public function testShouldSeeIntegrationSettingsWithCurrentValue(): void
    {
        $expectedValue = $this->faker->numberBetween(2, 8);

        [
            'token' => $token,
            'dealer' => $dealer,
            'dealer_integration' => $dealerIntegration
        ] = $this->createDealerIntegration(
            self::RACINGJUNK_CODE,
            ['settings' => 'a:2:{s:9:"dealer_id";s:4:"1004";s:7:"package";s:1:"' . $expectedValue . '";}']
        );

        $response = $this->withHeaders(['access-token' => $token->access_token])
            ->json(
                static::VERB,
                str_replace('{id}', $dealerIntegration->integration_id, static::ENDPOINT)
            );

        $response->assertStatus(200);

        $json = json_decode($response->content(), true);

        self::assertArrayHasKey('data', $json);
        self::assertArrayHasKey('settings', $json['data']);
        self::assertArrayHasKey('value', $json['data']['settings']['package']);
        self::assertEquals($expectedValue, $json['data']['settings']['package']['value']);

        $this->tearDownSeed($dealer->dealer_id);
    }

    public function testShouldSeeRacingJunkUsedSlots(): void
    {
        $this->shouldSeeUsedSlotsFor(self::RACINGJUNK_CODE);
    }

    private function shouldSeeUsedSlotsFor(string $integrationCode): void
    {
        $expectedUsedSlots = 3;

        [
            'token' => $token,
            'dealer' => $dealer,
            'dealer_integration' => $dealerIntegration
        ] = $this->createDealerIntegration(
            $integrationCode,
            ['settings' => 'a:2:{s:9:"dealer_id";s:4:"1004";s:7:"package";s:1:"2";}']
        );

        $integrationCode = str_replace('online', '', $integrationCode);

        factory(Inventory::class, 5)->create(['dealer_id' => $dealer->dealer_id, 'show_on_' . $integrationCode => 0]);
        factory(Inventory::class, $expectedUsedSlots)->create(['dealer_id' => $dealer->dealer_id, 'show_on_' . $integrationCode => 1]);

        $response = $this->withHeaders(['access-token' => $token->access_token])
            ->json(
                static::VERB,
                str_replace('{id}', $dealerIntegration->integration_id, static::ENDPOINT)
            );

        $response->assertStatus(200);

        $json = json_decode($response->content(), true);

        self::assertArrayHasKey('data', $json);
        self::assertArrayHasKey('values', $json['data']);
        self::assertArrayHasKey('used_slots', $json['data']['values']);
        self::assertEquals($expectedUsedSlots, $json['data']['values']['used_slots']);

        $this->tearDownSeed($dealer->dealer_id);
    }
}
