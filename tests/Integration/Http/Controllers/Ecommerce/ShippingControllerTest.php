<?php
namespace Tests\Integration\Http\Controllers\Ecommerce;

use Tests\database\seeds\Ecommerce\ShippingSeeder;
use Tests\TestCase;

class ShippingControllerTest extends TestCase
{
    public function testCalculateCosts()
    {
        $shippingSeeder = new ShippingSeeder();
        $shippingSeeder->seed();

        $response = $this->json("POST", '/api/ecommerce/shipping-costs',
            [
                'shipping_details' => $shippingSeeder->shippingDetails,
                'items' => $shippingSeeder->products
            ],
            [
                'access-token' => $shippingSeeder->authToken->access_token
            ]
        );

        $response->assertStatus(200);

        $responseJson = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('cost', $responseJson);
        $this->assertArrayHasKey('tax', $responseJson);
        $this->assertArrayHasKey('cart_id', $responseJson);
        $this->assertArrayHasKey('customer_id', $responseJson);
        $this->assertArrayHasKey('items', $responseJson);
        $this->assertArrayHasKey('method_code', $responseJson);
        $this->assertArrayHasKey('carrier_code', $responseJson);

        $this->assertNull($responseJson['customer_id']);
        $this->assertCount(2, $responseJson['items']);

        $this->assertSame($shippingSeeder->products[0]['sku'], $responseJson['items'][0]['sku']);

        $this->assertEquals(0, $responseJson['cost']);
        $this->assertEquals(0, $responseJson['tax']);
    }

    public function testFailAuthKey()
    {
        $shippingSeeder = new ShippingSeeder();
        $shippingSeeder->seed();

        $response = $this->json("POST", '/api/ecommerce/shipping-costs',
            [
                'shipping_details' => $shippingSeeder->shippingDetails,
                'items' => $shippingSeeder->products
            ]
        );

        $response->assertStatus(403);
    }
}
