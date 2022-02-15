<?php

namespace Tests\Feature\Website\Config;

use Tests\TestCase;
use App\Models\Website\Website;
use App\Models\Website\Config\WebsiteConfig;
use App\Models\User\User;
use App\Models\User\AuthToken;



class WebsiteConfigTest extends TestCase
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
          ->put('api/website/' . $this->website->id . '/website-config', ['home/bargain_listings/title' => 'example bargain title']);
      
      $json = json_decode($response->getContent(), true);

      self::assertIsArray($json[0]);
      self::assertTrue($json[0]['website_id'] == $this->website->id);
      self::assertTrue($json[0]['key'] == 'home/bargain_listings/title');
      self::assertTrue($json[0]['value'] == 'example bargain title');
      
      // test update
      
      $responseUpdate = $this
          ->withHeaders(['access-token' => $this->accessToken()])
          ->put('api/website/' . $this->website->id . '/website-config', ['home/bargain_listings/title' => 'example bargain title 2']);
      
      $json2 = json_decode($responseUpdate->getContent(), true);
      
      $updatedWebsiteConfig = WebsiteConfig::where('website_id', $this->website->id)->where('key', 'home/bargain_listings/title')->where('value', 'example bargain title 2')->first();
      
      self::assertIsArray($json2[0]);
      self::assertTrue($json2[0]['website_id'] == $this->website->id);
      self::assertTrue($json2[0]['key'] == 'home/bargain_listings/title');
      self::assertTrue($json2[0]['value'] == 'example bargain title 2');

      self::assertInstanceOf(WebsiteConfig::class, $updatedWebsiteConfig);
      self::assertTrue($updatedWebsiteConfig['value'] == 'example bargain title 2');
    }

    public function testIndex()
    {
      
      $this->withHeaders(['access-token' => $this->accessToken()])
          ->put('api/website/' . $this->website->id . '/website-config', ['inventory/additional_description' => 'test additional description']);
      
      $response = $this
          ->withHeaders(['access-token' => $this->accessToken()])
          ->get('api/website/' . $this->website->id . '/website-config');
      
      $json = json_decode($response->getContent(), true);
      self::assertIsArray($json['data']);
      
      $recordExists = false;
      foreach ($json['data'] as $webisteConfig) {
        if ($webisteConfig['key'] == 'inventory/additional_description' && $webisteConfig['value'] == 'test additional description') {
          $recordExists = true;
          return;
        }
      }
      self::assertTrue($recordExists);
    }
    

}