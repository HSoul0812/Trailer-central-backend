<?php

namespace Tests\Feature\Inventory\CustomOverlay;

class ListCustomOverlaysTest extends EndpointCustomOverlaysTest
{
    protected const VERB = 'GET';
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
     * @group DMS
     * @group DMS_INVENTORY_CUSTOM_OVERLAY
     *
     * @return void
     */
    public function testItShouldListOnlyOwnedOverlays(): void
    {
        $otherSeed = $this->createDealerWithCustomerOverlays();

        ['overlays' => $overlays, 'token' => $token] = $this->seed;

        $response = $this->withHeaders(['access-token' => $token->access_token])->json(static::VERB, static::ENDPOINT);

        $response->assertStatus(200);


        $json = json_decode($response->getContent(), true);

        self::assertArrayHasKey('data', $json);
        self::assertCount(count($overlays), $json['data']);
        self::assertSame($overlays[0]->name, $json['data'][0]['name']);
        self::assertSame($overlays[0]->value, $json['data'][0]['value']);

        $this->tearDownSeed($otherSeed['dealer']->dealer_id);
    }
}
