<?php
namespace Tests\Integration\Http\Controllers\Website\User;

use App\Repositories\Website\WebsiteUserFavoriteInventoryRepository;
use App\Repositories\Website\WebsiteUserFavoriteInventoryRepositoryInterface;
use Illuminate\Http\JsonResponse;
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
        $this->inventorySeeder = new InventorySeeder([
            'withInventory' => true
        ]);
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
        $response = $this->json('POST', "/api/website/inventory/favorite", [
            'inventory_ids' => [
                $this->inventorySeeder->inventory->inventory_id
            ]
        ], ['access_token' => $this->websiteUserSeeder->websiteUser->token->access_token]);
        $response->assertJson([
            'data' => [
                [
                    'website_user_id' => $this->websiteUserSeeder->websiteUser->id,
                    'inventory_id' => $this->inventorySeeder->inventory->inventory_id
                ]
            ]
        ]);
        $this->assertDatabaseHas('website_user_favorite_inventory', [
            'website_user_id' => $this->websiteUserSeeder->websiteUser->id,
            'inventory_id' => $this->inventorySeeder->inventory->inventory_id
        ]);
    }

    public function testIndex() {
        $this->websiteUserSeeder->seed();
        $this->inventorySeeder->seed();
        $repository = app()->make(WebsiteUserFavoriteInventoryRepositoryInterface::class);
        $repository->create([
            'website_user_id' => $this->websiteUserSeeder->websiteUser->id,
            'inventory_id' => $this->inventorySeeder->inventory->inventory_id
        ]);
        $response = $this->json('GET', "/api/website/inventory/favorite");
        $response->assertStatus(JsonResponse::HTTP_OK);
        $response->assertJson([
            'data' => [
                [
                    'website_user_id' => $this->websiteUserSeeder->websiteUser->id,
                    'inventory_id' => $this->inventorySeeder->inventory->inventory_id
                ]
            ]
        ]);
    }

    public function testDelete() {
        $this->websiteUserSeeder->seed();
        $this->inventorySeeder->seed();
        $response = $this->json('DELETE', "/api/website/inventory/favorite", [
            'inventory_ids' => [
                $this->inventorySeeder->inventory->inventory_id
            ]
        ], ['access_token' => $this->websiteUserSeeder->websiteUser->token->access_token]);
        $response->assertStatus(JsonResponse::HTTP_OK);
    }
}
