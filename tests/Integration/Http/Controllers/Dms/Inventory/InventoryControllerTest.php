<?php

namespace Tests\Integration\Http\Controllers\Dms\Inventory;

use App\Exceptions\Tests\MissingTestDealerIdException;
use App\Models\Inventory\Inventory;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class InventoryControllerTest extends TestCase
{
    /**
     * Test that the findByStock method returns 422 if we send the stock
     * that doesn't exist
     *
     * @group DMS
     * @group DMS_INVENTORY
     *
     * @return void
     */
    public function testFindByStockReturns422IfNotFound()
    {
        $stock = Str::random();

        $response = $this->makeApiRequest($stock);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertSeeText("No inventory with the stock '$stock'.");
    }

    /**
     * Test that the findByStock method returns 200 if we send the stock
     * that exists
     *
     * @group DMS
     * @group DMS_INVENTORY
     *
     * @return void
     * @throws MissingTestDealerIdException
     */
    public function testFindByStockReturnInventoryUponFounding()
    {
        $stock = Str::random();

        $inventory = factory(Inventory::class)->create([
            'dealer_id' => $this->getTestDealerId(),
            'stock' => $stock,
        ]);

        $response = $this->makeApiRequest($stock);

        $response->assertOk();

        $inventoryResponse = $response->json('data');

        $this->assertEquals($inventoryResponse['id'], $inventory->getKey());
        $this->assertEquals($stock, $inventory->stock);
    }

    /**
     * Test that the findByStock method returns 200 if we send the stock
     * that exists
     *
     * @group DMS
     * @group DMS_INVENTORY
     *
     * @return void
     * @throws MissingTestDealerIdException
     */
    public function testFindByStockReturnInventoryWhenStockHasSpaces()
    {
        $stock = Str::random() . ' ' . Str::random();

        $inventory = factory(Inventory::class)->create([
            'dealer_id' => $this->getTestDealerId(),
            'stock' => $stock,
        ]);

        $encodedStock = str_replace(' ', '%20', $stock);

        $response = $this->makeApiRequest($encodedStock);

        $response->assertOk();

        $inventoryResponse = $response->json('data');

        $this->assertEquals($inventoryResponse['id'], $inventory->getKey());
        $this->assertEquals($stock, $inventory->stock);
    }

    /**
     * Make the API request
     *
     * @param string $stock
     * @return TestResponse
     */
    private function makeApiRequest(string $stock): TestResponse
    {
        return $this->json('GET', $this->getRoute($stock), [], [
            'access-token' => $this->accessToken(),
        ]);
    }

    /**
     * Get the stocks route
     *
     * @param string $stock
     * @return string
     */
    private function getRoute(string $stock): string
    {
        return "/api/inventory/stocks/$stock";
    }
}
