<?php

namespace Tests\Feature\Ecommerce\Refunds;

use App\Models\Ecommerce\CompletedOrder\CompletedOrder;

/**
 * Test cases for all unhappy paths
 */
class IssueRefundTest extends RefundTest
{
    protected const VERB = 'POST';
    protected const ENDPOINT = '/api/ecommerce/refunds/{order_id}';
    protected const SKIP_REFUND_CREATION = true;

    public function testShouldPreventAccessingWithoutAuthentication(): void
    {
        $this->json(
            static::VERB,
            str_replace('{order_id}', $this->faker->numberBetween(3000, 5000), static::ENDPOINT)
        )->assertStatus(403);
    }

    /**
     * @dataProvider badArgumentsProvider
     */
    public function testItShouldNotCreateRefundWhenTheArgumentsAreWrong(
        array  $parameters,
        int    $expectedHttpStatusCode,
        string $expectedMessage,
        array  $expectedErrorMessages
    ): void
    {
        $order_id = is_callable($parameters['order']) ? $parameters['order']($this->seed)->id : $parameters['order'];

        ['token' => $token] = $this->seed;

        $response = $this->withHeaders(['access-token' => $token->access_token])->json(
            self::VERB,
            str_replace('{order_id}', $order_id, static::ENDPOINT),
            $parameters
        );

        $response->assertStatus($expectedHttpStatusCode);

        $json = json_decode($response->getContent(), true);

        self::assertArrayHasKey('message', $json);
        self::assertArrayHasKey('errors', $json);
        self::assertSame($expectedMessage, $json['message']);
        self::assertSame($expectedErrorMessages, $json['errors']);
    }

    public function testItShouldNotCreateRefundWhenSomePartDoesNotExists(): void
    {
        $expectedHttpStatusCode = 422;
        $expectedMessage = 'Validation Failed';

        ['order' => $order, 'token' => $token] = $this->seed;

        $fakePartId = $this->faker->numberBetween(3, 600);

        $parameters = [
            'parts' => collect($order->parts)->map(function (array $part) use ($fakePartId): array {
                return ['id' => $fakePartId, 'qty' => $this->faker->numberBetween(2, 4)];
            })->toArray()
        ];

        $expectedErrorMessages = ['parts' => [sprintf('The refund part[%d] is not a placed part', $fakePartId)]];

        $response = $this->withHeaders(['access-token' => $token->access_token])->json(
            self::VERB,
            str_replace('{order_id}', $order->id, static::ENDPOINT),
            $parameters
        );

        $response->assertStatus($expectedHttpStatusCode);

        $json = json_decode($response->getContent(), true);

        self::assertArrayHasKey('message', $json);
        self::assertArrayHasKey('errors', $json);
        self::assertSame($expectedMessage, $json['message']);
        self::assertSame($expectedErrorMessages, $json['errors']);
    }

    public function testItShouldNotCreateRefundWhenSomePartQtyIsWrong(): void
    {
        $expectedHttpStatusCode = 422;
        $expectedMessage = 'Validation Failed';

        ['order' => $order, 'token' => $token] = $this->seed;

        $partId = null;
        $partQty = null;
        $partOriginalQty = null;

        $parameters = [
            'parts' => collect($order->parts)->take(1)->map(function (array $part) use (&$partId, &$partQty, &$partOriginalQty): array {
                $partId = $part['id'];
                $partOriginalQty = $part['qty'];
                $partQty = $partOriginalQty + 2;

                return ['id' => $partId, 'qty' => $partQty];
            })->toArray()
        ];

        $expectedErrorMessages = ['parts' => [sprintf('The refund part[%d] qty(%d) is greater than the purchase qty(%d)', $partId, $partQty, $partOriginalQty)]];

        $response = $this->withHeaders(['access-token' => $token->access_token])->json(
            self::VERB,
            str_replace('{order_id}', $order->id, static::ENDPOINT),
            $parameters
        );

        $response->assertStatus($expectedHttpStatusCode);

        $json = json_decode($response->getContent(), true);

        self::assertArrayHasKey('message', $json);
        self::assertArrayHasKey('errors', $json);
        self::assertSame($expectedMessage, $json['message']);
        self::assertSame($expectedErrorMessages, $json['errors']);
    }

    public function badArgumentsProvider(): array
    {
        $this->refreshApplication();
        $this->setUpTraits();

        $getOrder = static function (array $seed): CompletedOrder {
            return $seed['order'];
        };

        return [
            'Non existent order, no parts' => [
                ['order' => $this->faker->numberBetween(12000, 15000)],
                422,
                'Validation Failed',
                [
                    'order_id' => ['The selected order id is invalid.'],
                    'parts' => ['The parts field is required.']
                ]
            ],
            'Wrong order, bad parts' => [
                ['order' => $getOrder, 'parts' => 'Wrong status'],
                422,
                'Validation Failed',
                [
                    'parts' => ['The parts needs to be an array.']
                ]
            ],
            'Part id is required' => [
                ['order' => $getOrder, 'parts' => [['qty' => $this->faker->numberBetween(3, 8)]]],
                422,
                'Validation Failed',
                [
                    'parts.0.id' => ['The parts.0.id field is required.']
                ]
            ],
            'Part qty is required' => [
                ['order' => $getOrder, 'parts' => [['id' => $this->faker->numberBetween(3, 8)]]],
                422,
                'Validation Failed',
                [
                    'parts.0.qty' => ['The parts.0.qty field is required.']
                ]
            ],
            'Part qty is wrong' => [
                ['order' => $getOrder, 'parts' => [['id' => $this->faker->numberBetween(3, 8), 'qty' => 'wrong type']]],
                422,
                'Validation Failed',
                [
                    'parts.0.qty' => ['The parts.0.qty needs to be an integer.']
                ]
            ],
            'Wrong reason' => [
                ['order' => $getOrder, 'parts' => [['id' => $this->faker->numberBetween(3, 8), 'qty' => 'wrong type']], 'reason' => 'wrong reason'],
                422,
                'Validation Failed',
                [
                    'reason' => ['The selected reason is invalid.'],
                    'parts.0.qty' => ['The parts.0.qty needs to be an integer.']
                ]
            ]
        ];
    }
}
