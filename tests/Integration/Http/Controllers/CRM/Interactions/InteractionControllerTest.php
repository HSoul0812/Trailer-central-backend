<?php

namespace Tests\Integration\Http\Controllers\CRM\Interactions;

use Tests\Integration\IntegrationTestCase;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Tests\database\seeds\CRM\Interactions\InteractionSeeder;
use App\Models\CRM\Interactions\Interaction;
use Carbon\Carbon;

class InteractionControllerTest extends IntegrationTestCase {

    use DatabaseTransactions;

    /**
     * @group CRM
     */
    public function testCreateContactInteractionWithEmptyNote()
    {
        $seeder = new InteractionSeeder();
        $seeder->seed();

        $lead = $seeder->leads[0];
        $interactionTime = Carbon::now()->format('Y-m-d H:i:s');

        $response = $this->json(
            'PUT',
            '/api/leads/'. $lead->getKey() .'/interactions',
            [
                'interaction_type' => Interaction::TYPE_CONTACT,
                'interaction_time' => $interactionTime,
                'interaction_notes' => ''
            ],
            ['access-token' => $seeder->authToken->access_token]
        );

        $response->assertStatus(200)
            ->assertJsonMissingValidationErrors()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'user_id',
                    'type',
                    'time',
                    'notes',
                    'contact_name',
                    'username',
                    'to_no',
                    'interaction_time'
                ]
            ])
            ->assertJsonPath('data.type', Interaction::TYPE_CONTACT)
            ->assertJsonPath('data.interaction_time', $interactionTime);

        $seeder->cleanup();
    }

    /**
     * @group CRM
     */
    public function testCreateNonContactInteractionWithEmptyNote()
    {
        $seeder = new InteractionSeeder();
        $seeder->seed();

        $lead = $seeder->leads[0];
        $interactionTime = Carbon::now()->format('Y-m-d H:i:s');
        $interactionNote = md5(time());

        $response = $this->json(
            'PUT',
            '/api/leads/'. $lead->getKey() .'/interactions',
            [
                'interaction_type' => Interaction::TYPE_TASK,
                'interaction_time' => $interactionTime,
                'interaction_notes' => ''
            ],
            ['access-token' => $seeder->authToken->access_token]
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['interaction_notes']);

        $seeder->cleanup();
    }

    /**
     * @group CRM
     */
    public function testCreateNonContactInteraction()
    {
        $seeder = new InteractionSeeder();
        $seeder->seed();

        $lead = $seeder->leads[0];
        $interactionTime = Carbon::now()->format('Y-m-d H:i:s');
        $interactionNote = md5(time());

        $response = $this->json(
            'PUT',
            '/api/leads/'. $lead->getKey() .'/interactions',
            [
                'interaction_type' => Interaction::TYPE_TASK,
                'interaction_time' => $interactionTime,
                'interaction_notes' => $interactionNote
            ],
            ['access-token' => $seeder->authToken->access_token]
        );

        $response->assertStatus(200)
            ->assertJsonMissingValidationErrors()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'user_id',
                    'type',
                    'time',
                    'notes',
                    'contact_name',
                    'username',
                    'to_no',
                    'interaction_time'
                ]
            ])
            ->assertJsonPath('data.type', Interaction::TYPE_TASK)
            ->assertJsonPath('data.interaction_time', $interactionTime)
            ->assertJsonPath('data.notes', $interactionNote);

        $seeder->cleanup();
    }

    /**
     * @group CRM
     */
    public function testUpdateContactInteraction()
    {
        $seeder = new InteractionSeeder();
        $seeder->seed();

        $lead = $seeder->leads[0];
        $interaction = $seeder->dealerInteractions[0];
        $interactionTime = Carbon::now()->format('Y-m-d H:i:s');
        $interactionNote = md5(time());

        $response = $this->json(
            'POST',
            '/api/leads/'. $lead->getKey() .'/interactions/'. $interaction->getKey(),
            [
                'interaction_type' => Interaction::TYPE_CONTACT,
                'interaction_time' => $interactionTime,
                'interaction_notes' => ''
            ],
            ['access-token' => $seeder->authToken->access_token]
        );

        $response->assertStatus(200)
            ->assertJsonMissingValidationErrors()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'user_id',
                    'type',
                    'time',
                    'notes',
                    'contact_name',
                    'username',
                    'to_no',
                    'interaction_time'
                ]
            ])
            ->assertJsonPath('data.type', Interaction::TYPE_CONTACT)
            ->assertJsonPath('data.interaction_time', $interactionTime)
            ->assertJsonPath('data.notes', '');

        $seeder->cleanup();
    }

    /**
     * @group CRM
     */
    public function testUpdateNonContactInteractionWithEmptyNote()
    {
        $seeder = new InteractionSeeder();
        $seeder->seed();

        $lead = $seeder->leads[0];
        $interaction = $seeder->dealerInteractions[0];
        $interactionTime = Carbon::now()->format('Y-m-d H:i:s');

        $response = $this->json(
            'POST',
            '/api/leads/'. $lead->getKey() .'/interactions/'. $interaction->getKey(),
            [
                'interaction_type' => Interaction::TYPE_TASK,
                'interaction_time' => $interactionTime,
                'interaction_notes' => ''
            ],
            ['access-token' => $seeder->authToken->access_token]
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['interaction_notes']);

        $seeder->cleanup();
    }

    /**
     * @group CRM
     */
    public function testUpdateNonContactInteractionWithNote()
    {
        $seeder = new InteractionSeeder();
        $seeder->seed();

        $lead = $seeder->leads[0];
        $interaction = $seeder->dealerInteractions[0];

        $interactionTime = Carbon::now()->format('Y-m-d H:i:s');
        $interactionNote = md5(time());

        $response = $this->json(
            'POST',
            '/api/leads/'. $lead->getKey() .'/interactions/'. $interaction->getKey(),
            [
                'interaction_type' => Interaction::TYPE_TASK,
                'interaction_time' => $interactionTime,
                'interaction_notes' => $interactionNote
            ],
            ['access-token' => $seeder->authToken->access_token]
        );

        $response->assertStatus(200)
            ->assertJsonMissingValidationErrors()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'user_id',
                    'type',
                    'time',
                    'notes',
                    'contact_name',
                    'username',
                    'to_no',
                    'interaction_time'
                ]
            ])
            ->assertJsonPath('data.type', Interaction::TYPE_TASK)
            ->assertJsonPath('data.interaction_time', $interactionTime)
            ->assertJsonPath('data.notes', $interactionNote);

        $seeder->cleanup();
    }

    /**
     * @group CRM
     */
    public function testCloseInteraction()
    {
        $seeder = new InteractionSeeder();
        $seeder->seed();

        $lead = $seeder->leads[0];
        $interaction = $seeder->dealerInteractions[0];

        $interactionTime = Carbon::now()->format('Y-m-d H:i:s');
        $interactionNote = md5(time());

        $response = $this->json(
            'POST',
            '/api/leads/'. $lead->getKey() .'/interactions/'. $interaction->getKey(),
            [
                'is_closed' => 1
            ],
            ['access-token' => $seeder->authToken->access_token]
        );

        $response->assertStatus(200)
            ->assertJsonMissingValidationErrors();

        $response = $this->json(
            'GET',
            '/api/user/interactions/tasks',
            [],
            ['access-token' => $seeder->authToken->access_token]
        );

        $response->assertStatus(200)
            ->assertJsonMissingExact(['data.*.id' => $interaction->getKey()]);

        $seeder->cleanup();
    }
}