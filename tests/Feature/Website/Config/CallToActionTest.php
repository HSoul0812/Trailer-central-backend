<?php

namespace Tests\Feature\Website\Config;

use Tests\TestCase;
use App\Models\Website\Website;
use App\Models\Website\Config\WebsiteConfig;
use App\Models\User\User;
use App\Models\User\AuthToken;



class CallToActionTest extends TestCase
{

    
    protected $website;
    
    
    public function setUp(): void
    {
      parent::setUp();

      $this->website = factory(Website::class)->create();
    }

    public function testCreateOrUpdate()
    {
      
      $response = $this
          ->withHeaders(['access-token' => $this->accessToken()])
          ->put('api/website/' . $this->website->id . '/call-to-action', ['call-to-action/custom-text' => 'example custom text']);
      
      $json = json_decode($response->getContent(), true);

      self::assertIsArray($json[0]);
      self::assertTrue($json[0]['website_id'] == $this->website->id);
      self::assertTrue($json[0]['key'] == 'call-to-action/custom-text');
      self::assertTrue($json[0]['value'] == 'example custom text');
      
      // test update
      
      $responseUpdate = $this
          ->withHeaders(['access-token' => $this->accessToken()])
          ->put('api/website/' . $this->website->id . '/call-to-action', ['call-to-action/custom-text' => 'example custom text test 2']);
      
      $json2 = json_decode($responseUpdate->getContent(), true);
      
      $updatedWebsiteConfig = WebsiteConfig::where('website_id', $this->website->id)->where('key', 'call-to-action/custom-text')->where('value', 'example custom text test 2')->first();
      
      self::assertIsArray($json2[0]);
      self::assertTrue($json2[0]['website_id'] == $this->website->id);
      self::assertTrue($json2[0]['key'] == 'call-to-action/custom-text');
      self::assertTrue($json2[0]['value'] == 'example custom text test 2');

      self::assertInstanceOf(WebsiteConfig::class, $updatedWebsiteConfig);
      self::assertTrue($updatedWebsiteConfig['value'] == 'example custom text test 2');
    }

    public function testIndex()
    {
      
      $this->withHeaders(['access-token' => $this->accessToken()])
          ->put('api/website/' . $this->website->id . '/call-to-action', ['call-to-action/custom-text' => 'example custom text index']);
      
      $response = $this
          ->withHeaders(['access-token' => $this->accessToken()])
          ->get('api/website/' . $this->website->id . '/call-to-action');
      
      $json = json_decode($response->getContent(), true);
      self::assertIsArray($json['data']);
      
      $recordExists = false;
      foreach ($json['data'] as $webisteConfig) {
        if ($webisteConfig['key'] == 'call-to-action/custom-text' && $webisteConfig['value'] == 'example custom text index') {
          $recordExists = true;
          return;
        }
      }
      self::assertTrue($recordExists);
    }
    

}