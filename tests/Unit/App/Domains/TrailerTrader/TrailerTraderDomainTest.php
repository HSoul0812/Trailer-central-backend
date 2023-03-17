<?php

namespace Tests\Unit\App\Domains\TrailerTrader;

use App\Domains\TrailerTrader\TrailerTraderDomain;
use Tests\Common\TestCase;

class TrailerTraderDomainTest extends TestCase
{
    public function testItCanGetHostFromUrl(): void
    {
        $tt = resolve(TrailerTraderDomain::class);

        $this->assertEquals(
            expected: 'trailertrader.com',
            actual: $tt->getHostFromDomainString('https://trailertrader.com/something?name=fake'),
        );

        $this->assertNull(
            actual: $tt->getHostFromDomainString(''),
        );
    }

    public function testItCanCheckFrontendDomain(): void
    {
        $tt = resolve(TrailerTraderDomain::class);

        config(['trailertrader.domains.frontend' => [
            'abc.com',
        ]]);

        $this->assertTrue(
            condition: $tt->isFrontendDomain('https://abc.com/something?name=fake'),
        );

        $this->assertTrue(
            condition: $tt->isFrontendDomain('abc.com'),
        );

        $this->assertFalse(
            condition: $tt->isFrontendDomain('def.com'),
        );
    }

    public function testItCanCheckBackendDomain(): void
    {
        $tt = resolve(TrailerTraderDomain::class);

        config(['trailertrader.domains.backend' => [
            'abc.com',
        ]]);

        $this->assertTrue(
            condition: $tt->isBackendDomain('https://abc.com/something?name=fake'),
        );

        $this->assertTrue(
            condition: $tt->isBackendDomain('abc.com'),
        );

        $this->assertFalse(
            condition: $tt->isBackendDomain('def.com'),
        );
    }

    public function testItCanCheckTrailerTraderDomain(): void
    {
        $tt = resolve(TrailerTraderDomain::class);

        config([
            'trailertrader.domains.frontend' => [
                'abc.com',
            ],
            'trailertrader.domains.backend' => [
                'wow.com',
            ],
        ]);

        $this->assertTrue(
            condition: $tt->isTrailerTraderDomain('abc.com'),
        );

        $this->assertTrue(
            condition: $tt->isTrailerTraderDomain('wow.com'),
        );
    }
}
