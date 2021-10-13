<?php
namespace Tests\Integration\Http\Controllers\Website\User;

use Laravel\Lumen\Testing\DatabaseTransactions;
use Tests\database\seeds\Inventory\InventorySeeder;
use Tests\database\seeds\Website\User\WebsiteUserSeeder;
use Tests\TestCase;

class WebsiteUserFavoriteInventoryControllerTest extends TestCase {
    use DatabaseTransactions;

    /**
     * @var WebsiteUserSeeder $websiteUserSeeder
     */
    private $websiteUserSeeder;

    /**
     * @var InventorySeeder $inventorySeeder
     */
    private $inventorySeeder;

    public function setUp(): void
    {
        parent::setUp();
        $this->websiteUserSeeder = new WebsiteUserSeeder();
        $this->inventorySeeder = new InventorySeeder();
    }

    public function tearDown(): void
    {
        $this->websiteUserSeeder->cleanUp();
        $this->inventorySeeder->cleanUp();
        parent::tearDown();
    }

    public function testCreateSuccess() {
        $this->websiteUserSeeder->seed();
        $this->inventorySeeder->seed();

    }

    public function testIndex() {
        $this->websiteUserSeeder->seed();
        $this->inventorySeeder->seed();
    }

    public function testDelete() {
        $this->websiteUserSeeder->seed();
        $this->inventorySeeder->seed();

    }
}
