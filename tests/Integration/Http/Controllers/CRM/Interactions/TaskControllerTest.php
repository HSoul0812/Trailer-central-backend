<?php

namespace Tests\Integration\Http\Controllers\CRM\Interactions;

use Tests\Integration\IntegrationTestCase;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Tests\database\seeds\CRM\Interactions\InteractionSeeder;
use Carbon\Carbon;
use App\Transformers\CRM\Leads\LeadTransformer;
use App\Transformers\CRM\User\SalesPersonTransformer;

class TaskControllerTest extends IntegrationTestCase {

    use DatabaseTransactions;

    /**
     * @group CRM
     */
    public function testIndex()
    {
        $seeder = new InteractionSeeder();
        $seeder->seed();

        $response = $this->json(
            'GET',
            '/api/user/interactions/tasks',
            [],
            ['access-token' => $seeder->authToken->access_token]
        );

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'task_date',
                        'task_time',
                        'type',
                        'lead',
                        'notes',
                        'id',
                        'contact_name',
                        'sales_person',
                        'lead_id',
                        'sales_name',
                        'sales_person_id'
                    ]
                ],
                'meta' => [
                    'pagination' => [
                        'total',
                        'count',
                        'per_page',
                        'current_page',
                        'total_pages'
                    ]
                ]
            ])
            ->assertJsonCount(10, 'data.*');

        $dealerExpectedData = []; $salesPersonExpectedData = [];
        foreach ($seeder->salesPersonInteractions as $interaction) {

            $interactionTime = Carbon::parse($interaction->interaction_time);

            $salesPersonSingleExpectedData = [
                'task_date' => $interactionTime->format('Y-m-d'),
                'task_time' => $interactionTime->format('h:i A'),
                'type' => $interaction->interaction_type,
                'lead' => (new LeadTransformer())->transform($interaction->lead),
                'notes' => $interaction->interaction_notes,
                'id' => $interaction->interaction_id,
                'contact_name' => $interaction->lead->getFullNameAttribute(),
                'sales_person' => $interaction->leadStatus->salesPerson ? (new SalesPersonTransformer)->transform($interaction->leadStatus->salesPerson) : null,
                'lead_id' => $interaction->tc_lead_id,
                'sales_name' => $interaction->leadStatus->salesPerson ? $interaction->leadStatus->salesPerson->first_name .' '. $interaction->leadStatus->salesPerson->last_name : null,
                'sales_person_id' => $interaction->sales_person_id
            ];

            $dealerExpectedData[] = $salesPersonSingleExpectedData;
            $salesPersonExpectedData[] = $salesPersonSingleExpectedData;
        }

        $interactionIdsWithoutSalesperson = [];
        foreach ($seeder->dealerInteractions as $interaction) {

            $interactionTime = Carbon::parse($interaction->interaction_time);

            $dealerExpectedData[] = [
                'task_date' => $interactionTime->format('Y-m-d'),
                'task_time' => $interactionTime->format('h:i A'),
                'type' => $interaction->interaction_type,
                'lead' => (new LeadTransformer)->transform($interaction->lead),
                'notes' => $interaction->interaction_notes,
                'id' => $interaction->interaction_id,
                'contact_name' => $interaction->lead->getFullNameAttribute(),
                'sales_person' => $interaction->leadStatus->salesPerson ? (new SalesPersonTransformer)->transform($interaction->leadStatus->salesPerson) : null,
                'lead_id' => $interaction->tc_lead_id,
                'sales_name' => $interaction->leadStatus->salesPerson ? $interaction->leadStatus->salesPerson->first_name .' '. $interaction->leadStatus->salesPerson->last_name : null,
                'sales_person_id' => $interaction->sales_person_id
            ];

            $interactionIdsWithoutSalesperson[] = $interaction->interaction_id;
        }

        // Ordering MUST be done after generating the expected data
        // it also MUST be converted to avoid AM/PM issues
        usort($dealerExpectedData, function ($a, $b) {
            $first = strtotime("{$a['task_date']} {$a['task_time']}");
            $second = strtotime("{$b['task_date']} {$b['task_time']}");
            return $first <=> $second;
        });
        usort($salesPersonExpectedData, function ($a, $b) {
            $first = strtotime("{$a['task_date']} {$a['task_time']}");
            $second = strtotime("{$b['task_date']} {$b['task_time']}");
            return $first <=> $second;
        });

        $this->assertResponseDataEquals($response, $dealerExpectedData, false);

        $salespersonResponse = $this->json(
            'GET',
            '/api/user/interactions/tasks',
            [],
            ['access-token' => $seeder->salesPersonAuthToken->access_token]
        );

        $salespersonResponse->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'task_date',
                        'task_time',
                        'type',
                        'lead',
                        'notes',
                        'id',
                        'contact_name',
                        'sales_person',
                        'lead_id',
                        'sales_name',
                        'sales_person_id'
                    ]
                ],
                'meta' => [
                    'pagination' => [
                        'total',
                        'count',
                        'per_page',
                        'current_page',
                        'total_pages'
                    ]
                ]
            ])
            ->assertJsonCount(5, 'data.*');

        foreach ($interactionIdsWithoutSalesperson as $expectedMissingInteractionId) {
            $salespersonResponse->assertJsonMissingExact(['data.*.id' => $expectedMissingInteractionId]);
        }

        $this->assertResponseDataEquals($salespersonResponse, $salesPersonExpectedData, false);

        $seeder->cleanup();
    }
}
