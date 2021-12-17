<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace Tests\Unit\App\Services\Integrations\TrailerCentral\Console\Leads\LogService;

use App\Services\Integrations\TrailerCentral\Console\Leads\LogService;
use Database\Seeders\WithArtifacts;
use JsonException;
use PDO;
use stdClass;
use Tests\Unit\WithFaker;

/**
 * @covers \App\Services\Integrations\TrailerCentral\Console\Leads\LogService::mapToInsertString
 */
class MapToInsertStringTest extends LogServiceTestCase
{
    use WithFaker;
    use WithArtifacts;

    public function testWillThrowJsonException(): void
    {
        /** @var stdClass $lead */
        $lead = (object) $this->fromJson('trailer-central/leads.json')->random();
        $lead->malformedData = utf8_decode('ñáẃ');

        $isNotTheFirstImport = false;

        $dependency = $this->mockDependency();
        $pdo = $this->mockClassWithoutArguments(PDO::class);

        $dependency->expects($this->once())
            ->method('getPdo')
            ->willReturn($pdo);

        $serviceMock = $this->getMockBuilder(LogService::class)
            ->onlyMethods(['quote'])
            ->setConstructorArgs([$dependency])
            ->getMock();

        $serviceMock->expects($this->atLeast(1))
            ->method('quote');

        $this->expectException(JsonException::class);
        $this->expectExceptionMessage('Malformed UTF-8 characters, possibly incorrectly encoded');

        $this->invokeMethod(
            $serviceMock,
            'mapToInsertString',
            [$lead, $isNotTheFirstImport]
        );
    }

    public function testWillBuildTheSQLFragment(): void
    {
        /** @var stdClass $lead */
        $lead = (object) $this->fromJson('trailer-central/leads.json')->random();
        $isNotTheFirstImport = false;

        $dependency = $this->mockDependency();
        $pdo = $this->mockClassWithoutArguments(PDO::class);

        $dependency->expects($this->once())
            ->method('getPdo')
            ->willReturn($pdo);

        $serviceMock = $this->getMockBuilder(LogService::class)
            ->onlyMethods(['quote'])
            ->setConstructorArgs([$dependency])
            ->getMock();

        $serviceMock->expects($this->atLeast(1))
            ->method('quote')
            ->willReturnCallback(fn ($value) => "##$value##");

        $sqlFragment = $this->invokeMethod(
            $serviceMock,
            'mapToInsertString',
            [$lead, $isNotTheFirstImport]
        );

        $this->assertStringContainsString("##$lead->first_name##", $sqlFragment);
        $this->assertStringContainsString("##$lead->last_name##", $sqlFragment);
    }
}
