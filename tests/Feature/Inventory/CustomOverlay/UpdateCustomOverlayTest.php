<?php

/** @noinspection PhpUnhandledExceptionInspection */

namespace Tests\Feature\Inventory\CustomOverlay;

use Illuminate\Foundation\Testing\WithFaker;

class UpdateCustomOverlayTest extends EndpointCustomOverlaysTest
{
    use WithFaker;

    protected const VERB = 'POST';
    protected const ENDPOINT = '/api/inventory/overlay';

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

        $expectedValue = $this->faker->sentence();

        ['dealer' => $dealer, 'overlays' => $overlays, 'token' => $token] = $this->seed;

        $response = $this->withHeaders(['access-token' => $token->access_token])
            ->json(static::VERB, static::ENDPOINT, ['name' => 'overlay_1', 'value' => $expectedValue]);

        $response->assertStatus(200);

        $json = json_decode($response->getContent(), true);

        self::assertArrayHasKey('response', $json);
        self::assertArrayHasKey('status', $json['response']);

        $this->assertDatabaseHas('custom_overlays', [
            'dealer_id' => $dealer->dealer_id,
            'name' => 'overlay_1',
            'value' => $expectedValue,
        ])->assertDatabaseMissing('custom_overlays', [
            'dealer_id' => $dealer->dealer_id,
            'name' => 'overlay_1',
            'value' => $overlays[0]->value
        ]);

        $this->tearDownSeed($otherSeed['dealer']->dealer_id);
    }

    public function badArgumentsProvider(): array
    {
        return [
            'no name argument' => [['wrong_name' => 'overlay_1', 'value' => 'Some value'], 'name', 'message' => 'The name field is required.'],
            'bad name' => [['name' => 'overlay_1xxx', 'value' => 'Some value'], 'name', 'message' => 'The selected name is invalid.'],
            'too long value' => [['name' => 'overlay_1', 'value' => base64_encode(random_bytes(255))], 'value', 'message' => 'The value may not be greater than 255 characters.'],
        ];
    }
}
