<?php

namespace Tests\Integration\App\Api\ViewedDealer;

use App\Models\Dealer\ViewedDealer;
use Illuminate\Testing\Fluent\AssertableJson;
use Str;
use Symfony\Component\HttpFoundation\Response;
use Tests\Common\IntegrationTestCase;

class IndexViewedDealerTest extends IntegrationTestCase
{
    const INDEX_VIEWED_DEALER_ENDPOINT = '/api/viewed-dealers';

    /**
     * Test that the system returns validation error if we get the viewed-dealers
     * without providing a name
     *
     * @return void
     */
    public function testItReturnsValidationErrorWhenFetchWithoutName(): void
    {
        $this
            ->getJson(self::INDEX_VIEWED_DEALER_ENDPOINT)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertSeeText('The name field is required.');
    }

    /**
     * Test that the system returns the not found error when we fetch the name
     * that doesn't exist in the database
     *
     * @return void
     */
    public function testItReturnsNotFoundWhenFetchWithNonExistenceName(): void
    {
        $name = Str::random();

        $this
            ->getJson(self::INDEX_VIEWED_DEALER_ENDPOINT . "?name=$name")
            ->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertSeeText(
                value: "Not found dealer id from name '$name'.",
                escape: false
            );
    }

    /**
     * Test that the system can return the viewed dealer data when the name exists
     *
     * @return void
     */
    public function testItReturnsTheViewedDealerDataWhenTheGivenNameExists(): void
    {
        $viewedDealer = ViewedDealer::factory()->create();

        $this
            ->getJson(self::INDEX_VIEWED_DEALER_ENDPOINT . "?name=$viewedDealer->name")
            ->assertOk()
            ->assertJson(function (AssertableJson $json) use ($viewedDealer) {
                $json
                    ->where('data.id', $viewedDealer->id)
                    ->where('data.dealer_id', $viewedDealer->dealer_id)
                    ->where('data.name', (string) $viewedDealer->name)
                    ->etc();
            });
    }
}
