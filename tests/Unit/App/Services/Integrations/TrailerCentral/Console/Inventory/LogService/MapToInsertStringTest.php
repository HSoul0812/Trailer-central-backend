<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace Tests\Unit\App\Services\Integrations\TrailerCentral\Console\Inventory\LogService;

use App\Models\Inventory\InventoryLog;
use App\Services\Integrations\TrailerCentral\Console\Inventory\LogService;
use Database\Seeders\WithArtifacts;
use JsonException;
use PDO;
use stdClass;
use Tests\Unit\WithFaker;

/**
 * @covers \App\Services\Integrations\TrailerCentral\Console\Inventory\LogService::mapToInsertString
 */
class MapToInsertStringTest extends LogServiceTestCase
{
    use WithFaker;
    use WithArtifacts;

    public function testWillThrowJsonException(): void
    {
        /** @var stdClass $inventory */
        $inventory = (object) $this->fromJson('trailer-central/inventory.json')->random();
        $inventory->malformedData = utf8_decode('ñáẃ');

        $isNotTheFirstImport = false;

        $dependencies = $this->mockDependencies();
        $pdo = $this->mockClassWithoutArguments(PDO::class);

        $dependencies['connection']->expects($this->once())
            ->method('getPdo')
            ->willReturn($pdo);

        $serviceMock = $this->getMockBuilder(LogService::class)
            ->onlyMethods(['quote'])
            ->setConstructorArgs($dependencies)
            ->getMock();

        $serviceMock->expects($this->atLeast(3))
            ->method('quote');

        $this->expectException(JsonException::class);
        $this->expectExceptionMessage('Malformed UTF-8 characters, possibly incorrectly encoded');

        $this->invokeMethod(
            $serviceMock,
            'mapToInsertString',
            [$inventory, $isNotTheFirstImport]
        );
    }

    /**
     * Test that SUT will build an SQL fragment when it is the first synchronization, also, since it is the first time,
     * the event should be 'created', finally the status should be assigned as expected.
     */
    public function testWillBuildTheValuesForTheFirstTime(): void
    {
        /** @var stdClass $inventory */
        $inventory = (object) $this->fromJson('trailer-central/inventory.json')->random();
        $inventory->status = $this->faker->randomElement([2, 3, 4, 5, 6]); // sold
        $isNotTheFirstImport = false;

        $dependencies = $this->mockDependencies();
        $pdo = $this->mockClassWithoutArguments(PDO::class);

        $dependencies['connection']->expects($this->once())
            ->method('getPdo')
            ->willReturn($pdo);

        $serviceMock = $this->getMockBuilder(LogService::class)
            ->onlyMethods(['quote'])
            ->setConstructorArgs($dependencies)
            ->getMock();

        $serviceMock->expects($this->atLeast(3))
            ->method('quote')
            ->willReturnCallback(fn ($value) => "##$value##");

        $sqlFragment = $this->invokeMethod(
            $serviceMock,
            'mapToInsertString',
            [$inventory, $isNotTheFirstImport]
        );

        $this->assertStringContainsString('##created##', $sqlFragment);
        $this->assertStringContainsString('##sold##', $sqlFragment);
    }

    /**
     * @dataProvider changesProvider
     *
     * Test that SUT will build an SQL fragment when it is not the first synchronization,
     * also it should be an event according to the next biz logic premise:
     *
     *  (a) if it were changed any value except 'price', then the event will be 'updated'
     *  (b) if it was changed the 'price', then the event will be 'price-changed' no matter if there were another change
     *
     * Finally, the status should be assigned as expected
     *
     * @param array<string, string|int> $propertiesToBeChanged
     *
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function testWillBuildTheValuesForNotTheFirstTime(array $propertiesToBeChanged, string $expectedEventName): void
    {
        /** @var array $info */
        $info = $this->fromJson('trailer-central/inventory.json')->random();
        $info['status'] = 7; // available
        $inventory = $this->mockEloquent(InventoryLog::class, [
            'id' => $this->faker->randomNumber(),
            'trailercentral_id' => $info['inventory_id'],
            'event' => InventoryLog::EVENT_CREATED,
            'status' => InventoryLog::STATUS_AVAILABLE,
            'vin' => $info['vin'],
            'brand' => $info['brand'],
            'manufacturer' => $info['manufacturer'],
            'price' => $info['price'],
            'meta' => json_encode($info, JSON_THROW_ON_ERROR),
            'created_at' => now(),
        ]);
        $isNotTheFirstImport = true;

        foreach ($propertiesToBeChanged as $propertyToChange => $newValue) {
            $info[$propertyToChange] = $newValue;
        }

        $dependencies = $this->mockDependencies();
        $pdo = $this->mockClassWithoutArguments(PDO::class);

        $dependencies['connection']->expects($this->once())
            ->method('getPdo')
            ->willReturn($pdo);

        $dependencies['repository']->expects($this->once())
            ->method('lastByRecordId')
            ->willReturn($inventory);

        $serviceMock = $this->getMockBuilder(LogService::class)
            ->onlyMethods(['quote'])
            ->setConstructorArgs($dependencies)
            ->getMock();

        $serviceMock->expects($this->atLeast(3))
            ->method('quote')
            ->willReturnCallback(fn ($value) => "##$value##");

        $sqlFragment = $this->invokeMethod(
            $serviceMock,
            'mapToInsertString',
            [(object) $info, $isNotTheFirstImport]
        );

        $this->assertStringContainsString("##$expectedEventName##", $sqlFragment);
        $this->assertStringContainsString('##available##', $sqlFragment);
    }

    /**
     * Examples of all cases of changed for a single inventory.
     *
     * @return array<string, array<array, string>>
     *
     * @noinspection PhpArrayShapeAttributeCanBeAddedInspection
     */
    public function changesProvider(): array
    {
        return [                          // array $propertiesToBeChanged, string $expectedEventName
            'only name was changed' => [['name' => 'Super boat'], InventoryLog::EVENT_UPDATED],
            'only price was changed' => [['price' => 676767.06], InventoryLog::EVENT_PRICE_CHANGED],
            'price and name were changed' => [['price' => 676767.06, 'name' => 'Super boat'], InventoryLog::EVENT_PRICE_CHANGED],
        ];
    }
}
