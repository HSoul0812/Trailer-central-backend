<?php

/** @noinspection PhpUnhandledExceptionInspection */

namespace Tests\Feature\Inventory\Image;

use App\Jobs\Ecommerce\ProcessRefundOnPaymentGatewayJob;
use App\Jobs\Files\DeleteS3FilesJob;
use Illuminate\Support\Facades\Bus;

class BulkDestroyInventoryImageTest extends EndpointInventoryImageTest
{
    protected const VERB = 'DELETE';
    protected const ENDPOINT = '/api/inventory/:id/images';

    public function testItShouldPreventAccessingWithoutAuthentication(): void
    {
        $this->itShouldPreventAccessingWithoutAuthentication();
    }

    /**
     * @dataProvider badArgumentsProvider
     *
     * @param array $arguments
     * @param string $expectedFieldNameWithError
     * @param string $expectedFieldMessage
     * @param int $expectedHttpStatus
     * @param string $expectedMessage
     */
    public function testItShouldNotDestroyWhenTheArgumentsAreWrong(
        array  $arguments,
        string $expectedFieldNameWithError,
        string $expectedFieldMessage,
        int    $expectedHttpStatus,
        string $expectedMessage
    ): void
    {
        $otherSeed = $this->createDealerAndInventoryWithImages();

        ['token' => $token] = $this->seed;

        $inventoryId = isset($arguments['inventory']) && is_callable($arguments['inventory']) ? $arguments['inventory']($this->seed) : 1;
        $inventoryId = isset($arguments['foreign_inventory']) ? $arguments['foreign_inventory']($otherSeed) : $inventoryId;

        if (isset($arguments['image_ids'])) {
            $arguments['image_ids'] = is_callable($arguments['image_ids']) ? $arguments['image_ids']($this->seed) : $arguments['image_ids'];
        }

        if (isset($arguments['foreign_image_ids'])) {
            $arguments['image_ids'] = $arguments['foreign_image_ids']($otherSeed);
        }

        $response = $this->withHeaders(['access-token' => $token->access_token])
            ->json(
                static::VERB,
                str_replace(':id', $inventoryId, static::ENDPOINT),
                $arguments
            );

        $response->assertStatus($expectedHttpStatus);

        $json = json_decode($response->getContent(), true);

        self::assertArrayHasKey('message', $json);
        self::assertSame($expectedMessage, $json['message']);

        if ($expectedHttpStatus === 422) {
            self::assertArrayHasKey('errors', $json);
            self::assertArrayHasKey($expectedFieldNameWithError, $json['errors']);
            self::assertSame([$expectedFieldMessage], $json['errors'][$expectedFieldNameWithError]);
        }

        $this->tearDownSeed($otherSeed['dealer']->dealer_id);
    }


    public function testItShouldUpdateWhenTheArgumentsAreFine(): void
    {
        Bus::fake();

        ['token' => $token, 'inventory' => $inventory, 'images' => $images] = $this->seed;

        $response = $this->withHeaders(['access-token' => $token->access_token])
            ->json(
                static::VERB,
                str_replace(':id', $inventory->inventory_id, static::ENDPOINT),
                [
                    'image_ids' => $images->pluck('image_id')->toArray(),
                ]
            );

        $response->assertStatus(204);

        $json = json_decode($response->getContent(), true);

        self::assertEmpty($json);

        Bus::assertDispatched(DeleteS3FilesJob::class);
    }

    public function badArgumentsProvider(): array
    {
        $this->refreshApplication();
        $this->setUpFaker();

        $getInventory = static function (array $seed): int {
            return $seed['inventory']->inventory_id;
        };

        $getImages = static function (array $seed): array {
            return $seed['images']->pluck('image_id')->toArray();
        };

        return [
            'non exists inventory' => [['inventory' => $this->faker->numberBetween(1, 5)], 'inventory_id', 'The selected inventory id is invalid.', 422, 'Validation Failed'],
            'no images' => [['image_ids' => []], 'image_ids', 'The image ids field is required.', 422, 'Validation Failed'],
            'foreign inventory' => [['foreign_inventory' => $getInventory, 'inventory' => $getInventory, 'image_ids' => $getImages], '', '', 401, '401 Unauthorized'],
            'foreign image ids' => [['inventory' => $getInventory, 'foreign_image_ids' => $getImages], '', '', 401, '401 Unauthorized'],
            'wrong image ids' => [['inventory' => $getInventory, 'image_ids' => ['333xxx', 'wwaaa']], 'image_ids.0', 'The image_ids.0 needs to be an integer.', 422, 'Validation Failed']
        ];
    }
}