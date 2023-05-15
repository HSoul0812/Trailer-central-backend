<?php

namespace Tests\Integration\App\Api\ViewedDealer;

use App\Models\Dealer\ViewedDealer;
use Tests\Common\IntegrationTestCase;

class CreateViewedDealerTest extends IntegrationTestCase
{
    public const CREATE_VIEWED_DEALERS_ENDPOINT = '/api/viewed-dealers';

    /**
     * Test that the system can accept the nothing as payload.
     */
    public function testItCanAcceptNothingInThePayload(): void
    {
        // It's ok to send nothing, we'll just won't process it
        $this
            ->postJson(self::CREATE_VIEWED_DEALERS_ENDPOINT)
            ->assertOk();

        // It's ok to send nothing, we'll just won't process it
        $this
            ->postJson(self::CREATE_VIEWED_DEALERS_ENDPOINT, [
                'viewed_dealers' => [[]],
            ])
            ->assertOk();
    }

    /**
     * Test that the system will remove the duplicated name from the payload automatically
     * and keep only the first instance of that name.
     */
    public function testItCanRemoveDuplicateNameFromPayload(): void
    {
        $viewedDealer1 = [
            'dealer_id' => 1,
            'name' => 'duplicate',
            'inventory_id' => 1,
        ];
        $viewedDealer2 = [
            'dealer_id' => 2,
            'name' => 'duplicate',
            'inventory_id' => 2,
        ];
        $viewedDealer3 = [
            'dealer_id' => 3,
            'name' => 'not duplicate',
            'inventory_id' => 3,
        ];

        // The code should remove the 2nd one and only store the first one in the database
        $this
            ->postJson(self::CREATE_VIEWED_DEALERS_ENDPOINT, [
                'viewed_dealers' => [
                    $viewedDealer1,
                    $viewedDealer2,
                    $viewedDealer3,
                ],
            ])
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this
            ->assertDatabaseHas(ViewedDealer::class, $viewedDealer1)
            ->assertDatabaseMissing(ViewedDealer::class, $viewedDealer2)
            ->assertDatabaseHas(ViewedDealer::class, $viewedDealer3);
    }

    /**
     * Test that the system can store the good payload just fine
     * a good payload means no duplicate of name and dealer id
     * and the dealer id it doesn't duplicate with dealer id stored
     * in the database.
     */
    public function testItCanStoreGoodPayloadSuccessfully(): void
    {
        $viewedDealer1 = [
            'dealer_id' => 1,
            'name' => 'Dealer 1',
            'inventory_id' => 1,
        ];
        $viewedDealer2 = [
            'dealer_id' => 2,
            'name' => 'Dealer 2',
            'inventory_id' => 2,
        ];
        $viewedDealer3 = [
            'dealer_id' => 3,
            'name' => 'Dealer 3',
            'inventory_id' => 3,
        ];

        $this
            ->postJson(self::CREATE_VIEWED_DEALERS_ENDPOINT, [
                'viewed_dealers' => [
                    $viewedDealer1,
                    $viewedDealer2,
                    $viewedDealer3,
                ],
            ])
            ->assertOk()
            ->assertJsonCount(3, 'data');

        $this
            ->assertDatabaseHas(ViewedDealer::class, $viewedDealer1)
            ->assertDatabaseHas(ViewedDealer::class, $viewedDealer2)
            ->assertDatabaseHas(ViewedDealer::class, $viewedDealer3);
    }
}
