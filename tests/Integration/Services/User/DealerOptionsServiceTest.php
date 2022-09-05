<?php
namespace Tests\Integration\Services\User;

use App\Services\User\DealerOptionsServiceInterface;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\database\seeds\Website\WebsiteSeeder;

class DealerOptionsServiceTest extends \Tests\TestCase
{
    use DatabaseTransactions;
    /**
     * @var WebsiteSeeder
     */
    private $seeder;

    public function setUp(): void
    {
        parent::setUp();
        $this->seeder = new WebsiteSeeder();
        $this->seeder->seed();
    }

    public function tearDown(): void
    {
        $this->seeder->cleanUp();
        parent::tearDown();
    }

    /**
     * @group DMS
     * @group DMS_DEALER
     *
     * @return void
     */
    public function testActivateUserAccounts() {
        $service = $this->getConcreteService();
        $service->activateUserAccounts($this->seeder->dealer->getKey());
        $this->assertDatabaseHas('website_config', [
            'website_id' => $this->seeder->website->getKey(),
            'key' => 'general/user_accounts',
            'value' => 1
        ]);
    }

    /**
     * @group DMS
     * @group DMS_DEALER
     *
     * @return void
     */
    public function testDeactivateUserAccounts() {
        $service = $this->getConcreteService();
        $service->deactivateUserAccounts($this->seeder->dealer->getKey());
        $this->assertDatabaseHas('website_config', [
            'website_id' => $this->seeder->website->getKey(),
            'key' => 'general/user_accounts',
            'value' => 0
        ]);
    }

    protected function getConcreteService(): DealerOptionsServiceInterface
    {
        return $this->app->make(DealerOptionsServiceInterface::class);
    }
}
