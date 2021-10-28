<?php

namespace Tests\Feature\Website\Config;

use Tests\TestCase;
use App\Models\Website\Website;
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

    public function testconfigCallToAction()
    {

      $response = $this
          ->withHeaders(['access-token' => $this->accessToken()])
          ->put('api/website/' . $this->website->id . '/call-to-action', ['call-to-action/custom-text' => 'example custom text']);
      
      $json = json_decode($response->getContent(), true);

      self::assertIsArray($json[0]);
      self::assertTrue($json[0]['website_id'] == $this->website->id);
      self::assertTrue($json[0]['key'] == 'call-to-action/custom-text');
      self::assertTrue($json[0]['value'] == 'example custom text');
    }

    public function testgetCallToAction()
    {
      
      $this->withHeaders(['access-token' => $this->accessToken()])
          ->put('api/website/' . $this->website->id . '/call-to-action', ['call-to-action/custom-text' => 'example custom text']);
      
      $response = $this
          ->withHeaders(['access-token' => $this->accessToken()])
          ->get('api/website/' . $this->website->id . '/call-to-action');
      
      $json = json_decode($response->getContent(), true);
      self::assertIsArray($json[0]);
      
    }
    

}