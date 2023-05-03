<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Lead;

use GuzzleHttp\Client as GuzzleHttpClient;
use Tests\Common\FeatureTestCase;
use Tests\Unit\WithFaker;

class LeadTest extends FeatureTestCase
{
    use WithFaker;

    public function testCreateValidInventory(): void
    {
        $this->markTestSkipped('This test is skipped because it connects to TC');

        $client = new GuzzleHttpClient(['headers' => ['access-token' => config('services.trailercentral.access_token')]]);
        $this->setUpFaker();

        $urlInventory = config('services.trailercentral.api') . 'inventory/';
        $urlDealerLocation = config('services.trailercentral.api') . 'user/dealer-location';

        $newDealerLocationParams = [
            'dealer_id' => 1004,
            'contact' => 'test contact',
            'address' => 'test address',
            'city' => 'city test',
            'county' => 'county test',
            'region' => 'region test',
            'country' => 'US',
            'postalcode' => 'postal code test',
            'phone' => '112346',
            'name' => $this->faker->word(),
            'captcha' => 'captcha test',
        ];

        $responseDealerLocation = $client->request('PUT', $urlDealerLocation, ['query' => $newDealerLocationParams]);

        $responseDealerLocation = json_decode($responseDealerLocation->getBody()->getContents(), true);

        $newInventoryParams = [
            'entity_type_id' => 1,
            'dealer_id' => 1004,
            'dealer_identifier' => 1004,
            'entity_type' => 1,
            'dealer_location_identifier' => $responseDealerLocation['data']['id'],
            'title' => 'test title 2',
        ];

        $responseInventory = $client->request('PUT', $urlInventory, ['query' => $newInventoryParams]);
        $responseInventory = json_decode($responseInventory->getBody()->getContents(), true);

        $responseShowInventory = $this->get('/api/inventory/' . $responseInventory['response']['data']['id']);
        $responseShowInventory = json_decode($responseShowInventory->getContent(), true);

        $params = [
            'lead_types' => ['status' => 'inventory'],
            'first_name' => 'test name',
            'last_name' => 'test last name',
            'phone_number' => '1234567890',
            'comments' => 'test comments',
            'email_address' => 'test@tc.com',
            'website_id' => $responseShowInventory['data']['dealer']['website']['id'],
            'inquiry_type' => 'inventory',
            'inventory' => ['inventory_id' => $responseShowInventory['data']['id']],
            'dealer_location_id' => $responseDealerLocation['data']['id'],
        ];

        $response = $this->withHeaders(['access-token' => config('services.trailercentral.access_token')])
            ->put('api/leads/', $params);

        $json = json_decode($response->getContent(), true);

        self::assertIsArray($json['data']);
        $response->assertStatus(200);

        $this->assertTrue($params['lead_types']['status'] == $json['data']['lead_types'][0]);
        $this->assertTrue($params['first_name'] . ' ' . $params['last_name'] == $json['data']['name']);
        $this->assertTrue($params['comments'] == $json['data']['comments']);
        $this->assertTrue($params['email_address'] == $json['data']['email_address']);
    }

    public function testCreateInvalidInventory(): void
    {
        $this->markTestSkipped('This test is skipped because it connects to TC');

        $params = [
            'lead_types' => ['status' => ''],
            'first_name' => 'test name',
            'last_name' => 'test last name',
            'phone_number' => '1234567890',
            'comments' => 'test comments',
            'email_address' => 'test@tc.com',
        ];

        $response = $this->withHeaders(['access-token' => config('services.trailercentral.access_token')])
            ->put('api/leads/', $params);

        $response->assertStatus(422);
    }
}
