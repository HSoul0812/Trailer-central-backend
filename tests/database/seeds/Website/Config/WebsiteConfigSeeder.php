<?php

declare(strict_types=1);

namespace Tests\database\seeds\Website\Config;

use App\Models\Website\Website;
use App\Models\Website\Config\WebsiteConfig;
use App\Models\User\User;
use App\Traits\WithGetter;
use Tests\database\seeds\Seeder;

/**
 * @property-read User $dealer
 * @property-read Website $website
 * @property-read array<WebsiteConfig> $createdWebsiteConfig
 */
class WebsiteConfigSeeder extends Seeder
{
    use WithGetter;

    /**
     * @var User
     */
    protected $dealer;

    /**
     * @var Website
     */
    protected $website;

    /**
     * @var WebsiteConfig[]
     */
    protected $websiteConfig;

    public function seed(): void
    {
        $this->seedDealer();
        $this->seedWebsite();

        $dealerId = $this->dealer->getKey();
        $websiteId = $this->website->getKey();

        $this->websiteConfig = factory(WebsiteConfig::class, 1)->create(['value' => 1, 'key' => 'parts/ecommerce/enabled', 'website_id' => $websiteId]); // 1 new websiteconfig
    }

    public function seedDealer(): void
    {
        $this->dealer = factory(User::class)->create();
    }

    public function seedWebsite(): void
    {
        $this->website = factory(Website::class)->create();
    }

    public function cleanUp(): void
    {
        // Database clean up

        User::whereIn('dealer_id', $this->dealer->getKey())->delete();
        Website::whereIn('website_id', $this->website->getKey())->delete();
    }
}