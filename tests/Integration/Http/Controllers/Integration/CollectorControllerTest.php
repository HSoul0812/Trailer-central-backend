<?php

namespace Tests\Integration\Http\Controllers\Integration;

use App\Models\Integration\Collector\Collector;
use App\Models\Integration\Collector\CollectorSpecification;
use App\Models\Integration\Collector\CollectorSpecificationAction;
use App\Models\Integration\Collector\CollectorSpecificationRule;
use Tests\database\seeds\Integration\CollectorSeeder;
use Tests\TestCase;

/**
 * Class CollectorControllerTest
 * @package Tests\Integration\Http\Controllers\Integration
 *
 * Test for \App\Http\Controllers\v1\Integration\CollectorController
 *
 * @coversDefaultClass \App\Http\Controllers\v1\Integration\CollectorController
 */
class CollectorControllerTest extends TestCase
{
    /**
     * @covers ::index
     */
    public function testIndex()
    {
        $seeder = new CollectorSeeder();
        $seeder->seed();

        $collectorParams = $this->getCollectorParams($seeder);

        $collector = $collectorParams['collector'];
        $collectorSpecification = $collectorParams['collectorSpecification'];
        $collectorSpecificationAction = $collectorParams['collectorSpecificationAction'];
        $collectorSpecificationRule = $collectorParams['collectorSpecificationRule'];

        $response = $this->json('GET', '/api/integration/collectors');

        $response->assertStatus(200);

        $responseJson = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('data', $responseJson);

        $key = array_search($collector->process_name, array_column($responseJson['data'], 'process_name'));

        $this->assertNotEmpty($responseJson['data'][$key]);

        $testedCollector = $responseJson['data'][$key];

        $this->assertArrayHasKey('process_name', $testedCollector);
        $this->assertSame($collector->process_name, $testedCollector['process_name']);

        $this->assertNotEmpty($testedCollector['specifications']);
        $this->assertCount(1, $testedCollector['specifications']);
        $this->assertArrayHasKey('id', $testedCollector['specifications'][0]);
        $this->assertSame($collectorSpecification->id, $testedCollector['specifications'][0]['id']);

        $this->assertNotEmpty($testedCollector['specifications'][0]['actions']);
        $this->assertCount(1, $testedCollector['specifications'][0]['actions']);
        $this->assertArrayHasKey('id', $testedCollector['specifications'][0]['actions'][0]);
        $this->assertSame($collectorSpecificationAction->id, $testedCollector['specifications'][0]['actions'][0]['id']);

        $this->assertNotEmpty($testedCollector['specifications'][0]['rules']);
        $this->assertCount(1, $testedCollector['specifications'][0]['rules']);
        $this->assertArrayHasKey('id', $testedCollector['specifications'][0]['rules'][0]);
        $this->assertSame($collectorSpecificationRule->id, $testedCollector['specifications'][0]['rules'][0]['id']);

        $seeder->cleanUp();
    }

    /**
     * @covers ::index
     */
    public function testIndexWithFilters()
    {
        $seeder = new CollectorSeeder();
        $seeder->seed();

        $collectorParams = $this->getCollectorParams($seeder);

        $collector = $collectorParams['collector'];
        $collectorSpecification = $collectorParams['collectorSpecification'];
        $collectorSpecificationAction = $collectorParams['collectorSpecificationAction'];
        $collectorSpecificationRule = $collectorParams['collectorSpecificationRule'];

        $response = $this->json('GET', '/api/integration/collectors?filter[process_name][eq]=' . $collector->process_name);

        $response->assertStatus(200);

        $responseJson = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('data', $responseJson);
        $this->assertCount(1, $responseJson['data']);
        $this->assertNotEmpty($responseJson['data'][0]);

        $testedCollector = $responseJson['data'][0];

        $this->assertArrayHasKey('process_name', $testedCollector);
        $this->assertSame($collector->process_name, $testedCollector['process_name']);

        $this->assertNotEmpty($testedCollector['specifications']);
        $this->assertCount(1, $testedCollector['specifications']);
        $this->assertArrayHasKey('id', $testedCollector['specifications'][0]);
        $this->assertSame($collectorSpecification->id, $testedCollector['specifications'][0]['id']);

        $this->assertNotEmpty($testedCollector['specifications'][0]['actions']);
        $this->assertCount(1, $testedCollector['specifications'][0]['actions']);
        $this->assertArrayHasKey('id', $testedCollector['specifications'][0]['actions'][0]);
        $this->assertSame($collectorSpecificationAction->id, $testedCollector['specifications'][0]['actions'][0]['id']);

        $this->assertNotEmpty($testedCollector['specifications'][0]['rules']);
        $this->assertCount(1, $testedCollector['specifications'][0]['rules']);
        $this->assertArrayHasKey('id', $testedCollector['specifications'][0]['rules'][0]);
        $this->assertSame($collectorSpecificationRule->id, $testedCollector['specifications'][0]['rules'][0]['id']);

        $seeder->cleanUp();
    }

    /**
     * @param CollectorSeeder $seeder
     * @return array
     */
    private function getCollectorParams(CollectorSeeder $seeder): array
    {
        /** @var Collector $collector */
        $collector = factory(Collector::class)->create([
            'dealer_id' => $seeder->dealer->dealer_id,
            'dealer_location_id' => $seeder->dealerLocation->dealer_location_id
        ]);

        /** @var CollectorSpecification $collectorSpecification */
        $collectorSpecification = factory(CollectorSpecification::class)->create([
            'collector_id' => $collector->id,
        ]);

        /** @var CollectorSpecificationAction $collectorSpecificationAction */
        $collectorSpecificationAction = factory(CollectorSpecificationAction::class)->create([
            'collector_specification_id' => $collectorSpecification->id,
        ]);

        /** @var CollectorSpecificationRule $collectorSpecificationRule */
        $collectorSpecificationRule = factory(CollectorSpecificationRule::class)->create([
            'collector_specification_id' => $collectorSpecification->id,
        ]);

        return [
            'collector' => $collector,
            'collectorSpecification' => $collectorSpecification,
            'collectorSpecificationAction' => $collectorSpecificationAction,
            'collectorSpecificationRule' => $collectorSpecificationRule,
        ];
    }
}
