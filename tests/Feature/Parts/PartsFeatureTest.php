<?php

namespace Tests\Feature\Parts;

use Tests\TestCase;

/**
 * Tests that SUT response with the desired information
 *
 * @package Tests\Feature\Parts
 *
 * @todo add create, update, delete tests
 */
class PartsFeatureTest extends TestCase
{
    public function testIndexHttpOk(): void
    {
        $response = $this
            ->withHeaders(['access-token' => $this->accessToken()])
            ->get('/api/parts');

        $response->assertStatus(200);
    }

    public function testSearchHasDataProperty(): void
    {
        $response = $this
            ->withHeaders(['access-token' => $this->accessToken()])
            ->get('/api/parts');

        $json = json_decode($response->getContent(), true);

        self::assertTrue(isset($json['data']));
    }

    public function testPartHasPurchasesPropertyAndItIsArrayType(): void
    {
        $response = $this
            ->withHeaders(['access-token' => $this->accessToken()])
            ->get('/api/parts');

        $json = json_decode($response->getContent(), true);

        self::assertTrue(isset($json['data'][0]['purchases']));
        self::assertIsArray($json['data'][0]['purchases']);
    }

    public function testPartHasPurchasesOrderNotCompleted(): void
    {
        // Given I'm a dealer
        $dealer_id = 1001;
        // And I have part with certainly ID (stock number)
        $term = 7300029;

        $params = [
            'dealer_id' => $dealer_id,
            'per_page' => 1,
            'page' => 1,
            'query' => $term,
            'search_term' => $term,
            'naive_search' => 1
        ];

        // When I search using the stock number
        $response = $this
            ->withHeaders(['access-token' => $this->accessToken()])
            ->get('/api/parts/search?' . http_build_query($params));

        $json = json_decode($response->getContent(), true);

        // Then I should see a part with the property purchases and within it a
        // property named has_not_completed equal true
        self::assertTrue($json['data'][0]['purchases']['has_not_completed']);
    }


}
