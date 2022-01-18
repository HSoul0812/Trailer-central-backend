<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Lead;

use Tests\Common\FeatureTestCase;
use GuzzleHttp\Client as GuzzleHttpClient;
use Tests\Unit\WithFaker;

class LeadTest extends FeatureTestCase
{
    use WithFaker;

    public function testCreateValidInventory(): void
    {
      $params = [
        'lead_types' => ['status' => 'inventory'],
        'first_name' => 'test name',
        'last_name'  => 'test last name',
        'phone_number' => '1234567890',
        'comments'     => 'test comments',
        'email_address' => 'test@tc.com',
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
      $params = [
        'lead_types' => ['status' => ''],
        'first_name' => 'test name',
        'last_name'  => 'test last name',
        'phone_number' => '1234567890',
        'comments'     => 'test comments',
        'email_address' => 'test@tc.com',
      ];

      $response = $this->withHeaders(['access-token' => config('services.trailercentral.access_token')])
          ->put('api/leads/', $params);

      $response->assertStatus(422);
    }

}