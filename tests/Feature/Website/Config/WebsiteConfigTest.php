<?php

namespace Tests\Feature\Website\Config;

use Tests\TestCase;
use App\Models\Website\Website;
use App\Models\Website\Config\WebsiteConfig;
use App\Models\User\User;
use App\Models\User\AuthToken;

class WebsiteConfigTest extends TestCase
{
    /** @var User  */
    protected $dealer;

    /** @var Website  */
    protected $website;

    /** @var AuthToken  */
    protected $token;

    public function setUp(): void
    {
      parent::setUp();

        $this->dealer = factory(User::class)->create();
        $this->website = factory(Website::class)->create(['dealer_id' => $this->dealer->getDealerId()]);
        $this->token = factory(AuthToken::class)->create([
            'user_id' => $this->dealer->getDealerId(),
            'user_type' => AuthToken::USER_TYPE_DEALER,
        ]);
    }

    public function tearDown(): void
    {
        $this->token->delete();
        $this->website->delete();
        $this->dealer->delete();

        parent::tearDown();
    }

    public function testCreateOrUpdate()
    {
      $response = $this
          ->withHeaders(['access-token' => $this->token->access_token])
          ->put('api/website/' . $this->website->id . '/website-config', ['home/bargain_listings/title' => 'example bargain title']);

      $json = json_decode($response->getContent(), true);

      self::assertIsArray($json[0]);
      self::assertTrue($json[0]['website_id'] == $this->website->id);
      self::assertTrue($json[0]['key'] == 'home/bargain_listings/title');
      self::assertTrue($json[0]['value'] == 'example bargain title');

      // test update

      $responseUpdate = $this
          ->withHeaders(['access-token' => $this->token->access_token])
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

    /**
     * Tests that SUT response payload contains a specific value which was previously updated
     *
     * @return void
     */
    public function testContainsCustomValue()
    {
        $expectedConfigValue = 'test additional tagline value';
        $configKey = 'general/tagline';

        $this->withHeaders(['access-token' => $this->token->access_token])
            ->put('api/website/' . $this->website->id . '/website-config', [$configKey => $expectedConfigValue]);

        $response = $this
            ->withHeaders(['access-token' => $this->token->access_token])
            ->get('api/website/' . $this->website->id . '/website-config');

        $json = json_decode($response->getContent(), true);

        self::assertIsArray($json['data']);

        $recordExists = false;

        foreach ($json['data'] as $websiteConfigGroup) {
            foreach ($websiteConfigGroup as $websiteConfig) {
                if ($websiteConfig['key'] == $configKey && $websiteConfig['current_value'] == $expectedConfigValue) {
                    $recordExists = true;
                    break 2;
                }
            }
        }

        self::assertTrue($recordExists);
    }

    /**
     * Tests that SUT response payload contains the default value when it has not a custom value
     *
     * @return void
     */
    public function testContainsDefaultValue()
    {
        $expectedConfigValue = 'countDesc';
        $configKey = 'inventory/website_sidebar_filters_order';

        $response = $this
            ->withHeaders(['access-token' => $this->token->access_token])
            ->get('api/website/' . $this->website->id . '/website-config');

        $json = json_decode($response->getContent(), true);

        self::assertIsArray($json['data']);

        $recordExists = false;

        foreach ($json['data'] as $websiteConfigGroup) {
            foreach ($websiteConfigGroup as $websiteConfig) {
                if ($websiteConfig['key'] == $configKey &&
                    $websiteConfig['default_value'] == $expectedConfigValue &&
                    $websiteConfig['current_value'] == $expectedConfigValue) {
                    $recordExists = true;
                    break 2;
                }
            }
        }

        self::assertTrue($recordExists);
    }
}
