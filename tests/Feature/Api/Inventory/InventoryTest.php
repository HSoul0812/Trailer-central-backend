<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Parts;

use Tests\Common\FeatureTestCase;
use GuzzleHttp\Client as GuzzleHttpClient;
use Tests\Unit\WithFaker;

class InventoryTest extends FeatureTestCase
{
    use WithFaker;

    public function testIndexNoInteger(): void
    {

        $response = $this->get('/api/inventory/1.1');

        $json = json_decode($response->getContent(), true);

        $response->assertStatus(404);
    }
    
    public function testIndexInvalidId(): void
    {

        $response = $this->get('/api/inventory/0');

        $json = json_decode($response->getContent(), true);

        $response->assertStatus(422);
    }
    
    public function testIndexValidId(): void
    {

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
          'name'  => $this->faker->word()
        ];

        $responseDealerLocation = $client->request('PUT', $urlDealerLocation, ['query' => $newDealerLocationParams]);
        
        $responseDealerLocation = json_decode($responseDealerLocation->getBody()->getContents(), true);
        
        $newInventoryParams = [
          'entity_type_id' => 1,
          'dealer_id'      => 1004,
          'dealer_identifier' => 1004,
          'entity_type'    => 1,
          'dealer_location_identifier' => $responseDealerLocation['data']['id'],
          'title' => 'test title 2'
        ];
  
        $newInventoryParamsWidth = [
          'entity_type_id' => 1,
          'dealer_id'      => 1004,
          'dealer_identifier' => 1004,
          'entity_type'    => 1,
          'dealer_location_identifier' => $responseDealerLocation['data']['id'],
          'title' => 'test title 2',
          'width' => 100
        ];
        
        $response = $client->request('PUT', $urlInventory, ['query' => $newInventoryParams]);
        $responseWidth = $client->request('PUT', $urlInventory, ['query' => $newInventoryParamsWidth]);

        $statusCode = $response->getStatusCode();
        
        $this->assertTrue($statusCode == 201);
      
        $response = json_decode($response->getBody()->getContents(), true);
        $responseWidth = json_decode($responseWidth->getBody()->getContents(), true);

        $responseShowInventory = $this->get('/api/inventory/' . $response['response']['data']['id']);
        $responseShowInventory = json_decode($responseShowInventory->getContent(), true);
        
        $responseShowInventoryWidth = $this->get('/api/inventory/' . $responseWidth['response']['data']['id']);
        $responseShowInventoryWidth = json_decode($responseShowInventoryWidth->getContent(), true);

        $this->assertIsArray($responseShowInventory['data']);
        $this->assertArrayHasKey('url', $responseShowInventory['data']);
        $this->assertnull($responseShowInventory['data']['width']);
        
        $this->assertIsArray($responseShowInventoryWidth['data']);
        $this->assertArrayHasKey('url', $responseShowInventoryWidth['data']);
        $this->assertTrue($responseShowInventoryWidth['data']['width'] == $newInventoryParamsWidth['width']);

        
        $this->cleanTcTestRecords($responseDealerLocation['data']['id']);
    }
    
    private function cleanTcTestRecords(int $dealerLocationId)
    {
      $client = new GuzzleHttpClient(['headers' => ['access-token' => config('services.trailercentral.access_token')]]);
  
      $urlDeleteDealerLocation = config('services.trailercentral.api') . 'user/dealer-location/' . $dealerLocationId;
      
      $client->delete($urlDeleteDealerLocation);

    }

}