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

        $partIds = [
            $uniqueFaker->numberBetween(1, 1000),
            $uniqueFaker->numberBetween(1, 1000),
            $uniqueFaker->numberBetween(1, 1000)
        ];

        /** @var array<Part> $parts */
        $parts = new Collection([
            factory(Part::class)->make(['id' => $partIds[0]]),
            factory(Part::class)->make(['id' => $partIds[1]]),
            factory(Part::class)->make(['id' => $partIds[2]]),
        ]);

        $dependencies = new PaymentServiceDependencies();

        $dependencies->refundRepository
            ->shouldReceive('getPartsToBeRefunded')
            ->andReturn($parts)
            ->with($partIds)
            ->once();

        $service = $this->getMockBuilder(PaymentService::class)
            ->setConstructorArgs($dependencies->getOrderedArguments())
            ->getMock();

        $encodedParts = $this->invokeMethod($service, 'encodePartsToBeRefunded', [$partIds]);

        $this->assertCount(3, $encodedParts);
        $this->assertArrayHasKey('title', $encodedParts[1]);
        $this->assertSame($parts->get(1)->title, $encodedParts[1]['title']);
        $this->assertArrayHasKey('sku', $encodedParts[1]);
        $this->assertSame($parts->get(1)->sku, $encodedParts[1]['sku']);
    }
}
