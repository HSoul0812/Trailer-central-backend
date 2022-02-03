<?php

namespace Tests\Feature\Ecommerce\Refunds;

class ShowRefundTest extends RefundTest
{
    protected const VERB = 'GET';
    protected const ENDPOINT = '/api/ecommerce/refunds/{id}';

    public function testShouldPreventAccessingWithoutAuthentication(): void
    {
        $this->json(
            static::VERB,
            str_replace('{id}', $this->faker->numberBetween(3, 3000),static::ENDPOINT)
        )->assertStatus(403);
    }

    public function testShouldNotSeeAnotherRefundWhichBelongsToAnotherDealer(): void
    {
        $otherRefund = $this->createRefund();

        ['token' => $token] = $this->seed;

        $response = $this->withHeaders(['access-token' => $token->access_token])
            ->json(
                static::VERB,
                str_replace('{id}', $otherRefund['refund']->id,static::ENDPOINT)
            );

        $response->assertStatus(400);

        $this->tearDownSeed($otherRefund['dealer']->dealer_id);
    }

    public function testShouldSeeSingleRefund(): void
    {
        $otherRefund = $this->createRefund();

        ['refund' => $refund, 'token' => $token] = $this->seed;

        $response = $this->withHeaders(['access-token' => $token->access_token])
            ->json(
                static::VERB,
                str_replace('{id}', $refund->id,static::ENDPOINT)
            );

        $response->assertStatus(200);

        $json = json_decode($response->getContent(), true);

        self::assertArrayHasKey('data', $json);
        self::assertSame($refund->id, $json['data']['id']);
        self::assertSame($refund->parts, $json['data']['parts']);

        $this->tearDownSeed($otherRefund['dealer']->dealer_id);
    }
}
