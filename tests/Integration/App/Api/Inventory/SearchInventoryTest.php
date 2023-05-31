<?php

namespace Tests\Integration\App\Api\Inventory;

use App\DTOs\Inventory\TcEsResponseInventoryList;
use App\Services\Inventory\InventorySDKServiceInterface;
use Config;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery\MockInterface;
use Symfony\Component\HttpFoundation\Response;
use Tests\Common\IntegrationTestCase;

class SearchInventoryTest extends IntegrationTestCase
{
    public const SEARCH_INVENTORY_ENDPOINT = '/api/inventory';

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('inventory-sdk.url', 'https://abc.com');

        $this->mock(InventorySDKServiceInterface::class, function (MockInterface $mock) {
            $list = new TcEsResponseInventoryList();
            $list->aggregations = [];
            $list->limits = [];
            $list->inventories = new LengthAwarePaginator([], 1, 1);
            $mock->shouldReceive('list')->andReturn($list);
        });
    }

    public function testItReturnValidationErrorWithInvalidParams(): void
    {
        $this
            ->json('GET', self::SEARCH_INVENTORY_ENDPOINT, ['sort' => 'createdAt|aaa'])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertSeeText('The sort format is invalid.');

        $this
            ->json('GET', self::SEARCH_INVENTORY_ENDPOINT, ['exclude_stocks' => '12231'])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertSeeText('The exclude stocks must be an array.');
    }

    public function testItReturnValidResponseWithValidParams(): void
    {
        $this
            ->json('GET', self::SEARCH_INVENTORY_ENDPOINT, ['exclude_stocks' => ['12', '32']])
            ->assertStatus(Response::HTTP_OK);
    }
}
