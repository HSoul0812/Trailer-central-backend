<?php

namespace Tests\Integration\App\Api\ViewedDealer;

use App\Models\Dealer\ViewedDealer;
use Str;
use Symfony\Component\HttpFoundation\Response;
use Tests\Common\IntegrationTestCase;

class CreateViewedDealerTest extends IntegrationTestCase
{
    const CREATE_VIEWED_DEALERS_ENDPOINT = '/api/viewed-dealers';

    /**
     * Test that the system will return the validation error if we provide
     * the wrong payload to the API endpoint
     *
     * @return void
     */
    public function testItReturnsValidationErrorWithInvalidRequestBody(): void
    {
        $this
            ->postJson(self::CREATE_VIEWED_DEALERS_ENDPOINT)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertSeeText('The viewed_dealers field is required.');

        $this
            ->postJson(self::CREATE_VIEWED_DEALERS_ENDPOINT, [
                'viewed_dealers' => [[]],
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertSeeText('The viewed_dealers.0.dealer_id field is required.')
            ->assertSeeText('The viewed_dealers.0.name field is required.');
    }

    /**
     * Test that we get back the bad request error if we provide the dealer id that is
     * already exists in the database
     *
     * @return void
     */
    public function testItReturnsBadRequestErrorWhenProvideDuplicateDealerIdWithDb(): void
    {
        $viewedDealer = ViewedDealer::factory()->create();
        $dealerId = $viewedDealer->dealer_id;

        $this
            ->postJson(self::CREATE_VIEWED_DEALERS_ENDPOINT, [
                'viewed_dealers' => [[
                    'dealer_id' => $dealerId,
                    'name' => Str::random(),
                ]],
            ])
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertSeeText("Dealer ID $dealerId already exists in the database, operation aborted.");
    }

    /**
     * Test that the system returns bad request error when we provide duplicate dealer id
     * in the payload itself
     *
     * @return void
     */
    public function testItReturnsBadRequestErrorWhenProvideDuplicateDealerIdInThePayload(): void
    {
        $name1 = Str::random();
        $name2 = Str::random();

        $this
            ->postJson(self::CREATE_VIEWED_DEALERS_ENDPOINT, [
                'viewed_dealers' => [[
                    'dealer_id' => 1,
                    'name' => $name1,
                ], [
                    'dealer_id' => 1,
                    'name' => $name2,
                ]],
            ])
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertSeeText(
                value: "Dealer name '$name1' and '$name2' has the same dealer id of 1, operation aborted.",
                escape: false
            );
    }

    /**
     * Test that the system will remove the duplicated name from the payload automatically
     * and keep only the first instance of that name
     *
     * @return void
     */
    public function testItCanRemoveDuplicateNameFromPayload(): void
    {
        $viewedDealer1 = [
            'dealer_id' => 1,
            'name' => 'duplicate',
        ];
        $viewedDealer2 = [
            'dealer_id' => 2,
            'name' => 'duplicate',
        ];
        $viewedDealer3 = [
            'dealer_id' => 3,
            'name' => 'not duplicate',
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
     * in the database
     *
     * @return void
     */
    public function testItCanStoreGoodPayloadSuccessfully(): void
    {
        $viewedDealer1 = [
            'dealer_id' => 1,
            'name' => 'Dealer 1',
        ];
        $viewedDealer2 = [
            'dealer_id' => 2,
            'name' => 'Dealer 2',
        ];
        $viewedDealer3 = [
            'dealer_id' => 3,
            'name' => 'Dealer 3',
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
