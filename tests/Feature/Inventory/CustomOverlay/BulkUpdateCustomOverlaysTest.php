<?php

/** @noinspection PhpUnhandledExceptionInspection */

namespace Tests\Feature\Inventory\CustomOverlay;

use Illuminate\Foundation\Testing\WithFaker;

class BulkUpdateCustomOverlaysTest extends EndpointCustomOverlaysTest
{
    use WithFaker;

    protected const VERB = 'POST';
    protected const ENDPOINT = '/api/inventory/bulk-overlay';

    /**
     * @group DMS
     * @group DMS_INVENTORY_CUSTOM_OVERLAY
     *
     * @return void
     */
    public function testItShouldPreventAccessingWithoutAuthentication(): void
    {
        $this->itShouldPreventAccessingWithoutAuthentication();
    }

    /**
     * @dataProvider badArgumentsProvider
     *
     * @group DMS
     * @group DMS_INVENTORY_CUSTOM_OVERLAY
     *
     * @param array $arguments
     * @param string $fieldName
     * @param string $message
     */
    public function testItShouldNotUpdateWhenTheArgumentsAreWrong(
        array  $arguments,
        string $fieldName,
        string $message
    ): void
    {
        $otherSeed = $this->createDealerWithCustomerOverlays();

        ['token' => $token] = $this->seed;

        $response = $this->withHeaders(['access-token' => $token->access_token])
            ->json(static::VERB, static::ENDPOINT, $arguments);

        $response->assertStatus(422);

        $json = json_decode($response->getContent(), true);

        self::assertArrayHasKey('message', $json);
        self::assertArrayHasKey('errors', $json);
        self::assertArrayHasKey($fieldName, $json['errors']);
        self::assertSame('Validation Failed', $json['message']);
        self::assertSame([$message], $json['errors'][$fieldName]);

        $this->tearDownSeed($otherSeed['dealer']->dealer_id);
    }

    /**
     * @group DMS
     * @group DMS_INVENTORY_CUSTOM_OVERLAY
     *
     * @return void
     */
    public function testItShouldUpdateWhenTheArgumentsAreFine(): void
    {
        $otherSeed = $this->createDealerWithCustomerOverlays();

        $expectedValue1 = $this->faker->sentence();
        $expectedValue2 = $this->faker->sentence();

        ['dealer' => $dealer, 'overlays' => $overlays, 'token' => $token] = $this->seed;

        $response = $this->withHeaders(['access-token' => $token->access_token])
            ->json(static::VERB, static::ENDPOINT, [
                'overlays' => [
                    ['name' => 'overlay_1', 'value' => $expectedValue1],
                    ['name' => 'overlay_2', 'value' => $expectedValue2]
                ]
            ]);

        $response->assertStatus(200);

        $json = json_decode($response->getContent(), true);

        self::assertArrayHasKey('response', $json);
        self::assertArrayHasKey('status', $json['response']);

        $this->assertDatabaseHas('custom_overlays', [
            'dealer_id' => $dealer->dealer_id,
            'name' => 'overlay_1',
            'value' => $expectedValue1,
        ])->assertDatabaseMissing('custom_overlays', [
            'dealer_id' => $dealer->dealer_id,
            'name' => 'overlay_1',
            'value' => $overlays[0]->value
        ]);

        $this->assertDatabaseHas('custom_overlays', [
            'dealer_id' => $dealer->dealer_id,
            'name' => 'overlay_2',
            'value' => $expectedValue2,
        ])->assertDatabaseMissing('custom_overlays', [
            'dealer_id' => $dealer->dealer_id,
            'name' => 'overlay_2',
            'value' => $overlays[1]->value
        ]);

        $this->tearDownSeed($otherSeed['dealer']->dealer_id);
    }

    public function badArgumentsProvider(): array
    {
        return [
            'no overlays' => [['overlayes' => []], 'overlays', 'message' => 'The overlays field is required.'],
            'no name argument within overlay' => [['overlays' => [['wrong_name' => 'overlay_1', 'value' => 'Some value']]], 'overlays.0.name', 'message' => 'The overlays.0.name field is required.'],
            'bad name argument within overlay' => [['overlays' => [['name' => 'overlay_1x', 'value' => 'Some value']]], 'overlays.0.name', 'message' => 'The selected overlays.0.name is invalid.'],
            'too long value' => [['overlays' => [['name' => 'overlay_1', 'value' => base64_encode(random_bytes(255))]]], 'overlays.0.value', 'message' => 'The overlays.0.value may not be greater than 255 characters.'],
        ];
    }
}
