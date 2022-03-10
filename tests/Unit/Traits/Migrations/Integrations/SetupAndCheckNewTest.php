<?php

namespace Tests\Traits\Migrations\Integrations;

use App\Models\Integration\Integration;
use App\Traits\Migrations\Integrations\SetupAndCheckNew;
use Mockery;
use Tests\TestCase;


/**
 * Tests the trait `SetupAndCheckNew`.
 *
 * Class SetupAndCheckNewTest
 * @package Tests\Traits\Migrations\Integrations
 *
 * @coversDefaultClass \App\Traits\Migrations\Integrations\SetupAndCheckNew
 */
class SetupAndCheckNewTest extends TestCase
{

    /**
     * @covers ::getNextIdFromDb
     *
     * @return void
     */
    public function testGetNextIdFromDb(): void
    {
        $trait = $this->getMockForTrait(SetupAndCheckNew::class);

        $next = $trait->getNextIdFromDb();

        $this->assertGreaterThan(87, $next);
    }

    /**
     * @covers ::testGetNextId
     *
     * @param string $integrationName
     * @return void
     */
    public function testGetNextId(string $integrationName = 'ExternalTCDealerClient'): void
    {
        $trait = $this->getMockForTrait(SetupAndCheckNew::class);

        $next = $trait->getNextId($integrationName);

        $this->assertGreaterThan(87, $next);
    }

    /**
     * @covers ::testGetNextId
     *
     * @param string $integrationName
     * @return void
     */
    public function testGetNextIdWithExisting(string $integrationName = 'CarGurus'): void
    {
        $trait = $this->getMockForTrait(SetupAndCheckNew::class);

        $this->assertEquals(0, $trait->getNextId($integrationName));
    }

}
