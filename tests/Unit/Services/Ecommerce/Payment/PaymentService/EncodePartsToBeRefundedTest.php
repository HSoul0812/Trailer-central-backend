<?php

/** @noinspection PhpDocMissingThrowsInspection */
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace Tests\Unit\Services\Ecommerce\Payment\PaymentService;

use App\Models\Parts\Textrail\Part;
use App\Services\Ecommerce\Payment\PaymentService;
use Illuminate\Support\Collection;

/**
 * @covers \App\Services\Ecommerce\Payment\PaymentService::encodePartsToBeRefunded
 */
class EncodePartsToBeRefundedTest extends PaymentServiceTestCase
{
    /**
     * Test that SUT will return a well structure array
     */
    public function testItWillBuildStructuredArray(): void
    {
        $uniqueFaker = $this->faker->unique(true);

        $partIdGenerator = static function () use ($uniqueFaker): int {
            return $uniqueFaker->numberBetween(1, 1000);
        };

        /** @var Collection|array<Part> $parts */
        $parts = factory(Part::class, 3)->make(['id' => $partIdGenerator]);

        $partsToRefund = $parts->map(function (Part $part): array {
            return ['id' => $part->id, 'amount' => $this->faker->numberBetween(4, 40)];
        })->keyBy('id')->toArray();

        $dependencies = new PaymentServiceDependencies();

        $dependencies->refundRepository
            ->shouldReceive('getPartsToBeRefunded')
            ->andReturn($parts)
            ->with(array_keys($partsToRefund))
            ->once();

        $service = $this->getMockBuilder(PaymentService::class)
            ->setConstructorArgs($dependencies->getOrderedArguments())
            ->getMock();

        $encodedParts = $this->invokeMethod($service, 'encodePartsToBeRefunded', [$partsToRefund]);

        $this->assertCount(3, $encodedParts);
        $this->assertArrayHasKey('title', $encodedParts[1]);
        $this->assertSame($parts->get(1)->title, $encodedParts[1]['title']);
        $this->assertArrayHasKey('sku', $encodedParts[1]);
        $this->assertSame($parts->get(1)->sku, $encodedParts[1]['sku']);
        $this->assertSame($partsToRefund[$parts->get(1)->id]['amount'], $encodedParts[1]['amount']);
    }
}
