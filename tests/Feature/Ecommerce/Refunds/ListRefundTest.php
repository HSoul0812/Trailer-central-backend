<?php

namespace Tests\Feature\Ecommerce\Refunds;

class ListRefundTest extends RefundTest
{
    protected const VERB = 'GET';
    protected const ENDPOINT = '/api/ecommerce/refunds';

    public function testShouldPreventAccessingWithoutAuthentication(): void
    {
        $this->itShouldPreventAccessingWithoutAuthentication();
    }

    public function testShouldSeeListOfRefunds(): void
    {
        $otherRefund = $this->createRefund();

        ['refund' => $refund, 'token' => $token] = $this->seed;

        $response = $this->withHeaders(['access-token' => $token->access_token])
            ->json(static::VERB, static::ENDPOINT);

        $response->assertStatus(200);

        $json = json_decode($response->getContent(), true);

        self::assertSame([
            "pagination" => [
                "total" => 1,
                "count" => 1,
                "per_page" => 100,
                "current_page" => 1,
                "total_pages" => 1,
                "links" => []
            ]
        ], $json['meta']);

        self::assertArrayHasKey('data', $json);
        self::assertIsArray($json['data']);
        self::assertSame($refund->id, $json['data'][0]['id']);

        $this->tearDownSeed($otherRefund['dealer']->dealer_id);
    }
}
