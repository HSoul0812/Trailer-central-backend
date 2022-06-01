<?php

namespace Tests\Integration\Http\Controllers\CRM\Interactions;

use App\Models\CRM\Interactions\InteractionMessage;
use Tests\database\seeds\CRM\Interactions\InteractionMessageSeeder;
use Tests\database\seeds\User\UserSeeder;
use Tests\Integration\IntegrationTestCase;

/**
 * Class InteractionMessageControllerTest
 * @package Tests\Integration\Http\Controllers\CRM\Interactions
 *
 * @coversDefaultClass \App\Http\Controllers\v1\CRM\Interactions\InteractionMessageController
 */
class InteractionMessageControllerTest extends IntegrationTestCase
{
    /**
     * @group CRM
     * @covers ::search
     */
    public function testSearch()
    {
        $userSeeder = new UserSeeder();
        $userSeeder->seed();

        $interactionMessageParams = ['page' => 1];

        $response = $this->json(
            'GET',
            '/api/leads/interaction-message/search',
            $interactionMessageParams,
            ['access-token' => $userSeeder->authToken->access_token]
        );

        $response->assertStatus(200);

        $this->assertEmptyResponseData($response);

        $interactionMessageSeeder = new InteractionMessageSeeder($userSeeder->dealer);
        $interactionMessageSeeder->seed();

        // I don't like it solution. But, due to that the insert to es is asynchronous, I was forced to add a timeout.
        sleep(1);

        $interactionMessageParams['message_type'] = InteractionMessage::MESSAGE_TYPE_EMAIL;
        $emailHistoryResponse = $this->json(
            'GET',
            '/api/leads/interaction-message/search',
            $interactionMessageParams,
            ['access-token' => $userSeeder->authToken->access_token]
        );

        $emailHistoryResponse->assertStatus(200);

        $expectedData = [
            'message_type' => InteractionMessage::MESSAGE_TYPE_EMAIL,
            'lead_id' => $interactionMessageSeeder->lead->identifier,
            'dealer_id' => $userSeeder->dealer->dealer_id,
            'lead_first_name' => $interactionMessageSeeder->lead->first_name,
            'lead_last_name' => $interactionMessageSeeder->lead->last_name,
        ];

        $this->assertResponseDataEquals($emailHistoryResponse, $expectedData);

        $interactionMessageParams['message_type'] = InteractionMessage::MESSAGE_TYPE_SMS;
        $textLogResponse = $this->json('GET', '/api/leads/interaction-message/search', $interactionMessageParams, ['access-token' => $userSeeder->authToken->access_token]);
        $textLogResponse->assertStatus(200);

        $expectedData = [
            'message_type' => InteractionMessage::MESSAGE_TYPE_SMS,
            'lead_id' => $interactionMessageSeeder->lead->identifier,
            'dealer_id' => $userSeeder->dealer->dealer_id,
            'lead_first_name' => $interactionMessageSeeder->lead->first_name,
            'lead_last_name' => $interactionMessageSeeder->lead->last_name,
        ];

        $this->assertResponseDataEquals($textLogResponse, $expectedData);

        $interactionMessageSeeder->cleanUp();
        $userSeeder->cleanUp();
    }

    /**
     * @group CRM
     * @covers ::search
     */
    public function testSearchWithWrongAccessToken()
    {
        $interactionMessageParams = ['page' => 1];

        $response = $this->json(
            'GET',
            '/api/leads/interaction-message/search',
            $interactionMessageParams,
            ['access-token' => 'wrong_access_token']
        );

        $response
            ->assertStatus(403)
            ->assertSee('Invalid access token.');
    }

    /**
     * @group CRM
     * @covers ::search
     */
    public function testSearchWithoutPageParam()
    {
        $userSeeder = new UserSeeder();
        $userSeeder->seed();

        $response = $this->json(
            'GET',
            '/api/leads/interaction-message/search',
            [],
            ['access-token' => $userSeeder->authToken->access_token]
        );

        $response->assertStatus(422);

        $userSeeder->cleanUp();
    }

    /**
     * @group CRM
     * @covers ::searchCountOf
     */
    public function testSearchCountOf()
    {
        $userSeeder = new UserSeeder();
        $userSeeder->seed();

        $response = $this->json(
            'GET',
            '/api/leads/interaction-message/search/count-of/message_type',
            [],
            ['access-token' => $userSeeder->authToken->access_token]
        );

        $response->assertStatus(200);

        $expectedData = [
            'email' => 0,
            'sms' => 0,
            'fb' => 0
        ];

        $this->assertResponseDataEquals($response, $expectedData, false);

        $interactionMessageSeeder = new InteractionMessageSeeder($userSeeder->dealer);
        $interactionMessageSeeder->seed();

        // I don't like it solution. But, due to that the insert to es is asynchronous, I was forced to add a timeout.
        sleep(1);

        $response = $this->json(
            'GET',
            '/api/leads/interaction-message/search/count-of/message_type',
            [],
            ['access-token' => $userSeeder->authToken->access_token]
        );

        $response->assertStatus(200);

        $expectedData = [
            'email' => 1,
            'sms' => 1,
            'fb' => 0
        ];

        $this->assertResponseDataEquals($response, $expectedData, false);

        $interactionMessageSeeder->cleanUp();
        $userSeeder->cleanUp();
    }

    /**
     * @group CRM
     * @covers ::searchCountOf
     */
    public function testSearchCountOfWithWrongAccessToken()
    {
        $response = $this->json(
            'GET',
            '/api/leads/interaction-message/search/count-of/message_type',
            [],
            ['access-token' => 'wrong_access_token']
        );

        $response
            ->assertStatus(403)
            ->assertSee('Invalid access token.');
    }

    /**
     * @group CRM
     * @covers ::update
     */
    public function testUpdate()
    {
        $userSeeder = new UserSeeder();
        $userSeeder->seed();

        $interactionMessageSeeder = new InteractionMessageSeeder($userSeeder->dealer);
        $interactionMessageSeeder->seed();

        $emailInteractionId = $interactionMessageSeeder->emailInteractionMessage->id;
        $textInteractionId = $interactionMessageSeeder->textInteractionMessage->id;

        $this->assertDatabaseHas('interaction_message', ['id' => $emailInteractionId, 'hidden' => 0]);
        $this->assertDatabaseHas('interaction_message', ['id' => $textInteractionId, 'hidden' => 0]);

        $response = $this->json(
            'POST',
            '/api/leads/interaction-message/' . $interactionMessageSeeder->emailInteractionMessage->id,
            ['hidden' => 1],
            ['access-token' => $userSeeder->authToken->access_token]
        );

        $this->assertUpdateResponse($response);

        $this->assertDatabaseHas('interaction_message', ['id' => $emailInteractionId, 'hidden' => 1]);
        $this->assertDatabaseHas('interaction_message', ['id' => $textInteractionId, 'hidden' => 0]);

        $response = $this->json(
            'POST',
            '/api/leads/interaction-message/' . $interactionMessageSeeder->textInteractionMessage->id,
            ['hidden' => 1],
            ['access-token' => $userSeeder->authToken->access_token]
        );

        $this->assertUpdateResponse($response);

        $this->assertDatabaseHas('interaction_message', ['id' => $emailInteractionId, 'hidden' => 1]);
        $this->assertDatabaseHas('interaction_message', ['id' => $textInteractionId, 'hidden' => 1]);

        $interactionMessageSeeder->cleanUp();
        $userSeeder->cleanUp();
    }

    /**
     * @group CRM
     * @covers ::update
     */
    public function testUpdateWithWrongAccessToken()
    {
        $userSeeder = new UserSeeder();
        $userSeeder->seed();

        $interactionMessageSeeder = new InteractionMessageSeeder($userSeeder->dealer);
        $interactionMessageSeeder->seed();

        $response = $this->json(
            'POST',
            '/api/leads/interaction-message/' . $interactionMessageSeeder->textInteractionMessage->id,
            ['hidden' => 1],
            ['access-token' => 'wrong_access_token']
        );

        $response
            ->assertStatus(403)
            ->assertSee('Invalid access token.');

        $interactionMessageSeeder->cleanUp();
        $userSeeder->cleanUp();
    }

    /**
     * @group CRM
     * @covers ::bulkUpdate
     */
    public function testBulkUpdate()
    {
        $userSeeder = new UserSeeder();
        $userSeeder->seed();

        $interactionMessageSeeder = new InteractionMessageSeeder($userSeeder->dealer);
        $interactionMessageSeeder->seed();

        $emailInteractionId = $interactionMessageSeeder->textInteractionMessage->id;
        $textInteractionId = $interactionMessageSeeder->emailInteractionMessage->id;

        $this->assertDatabaseHas('interaction_message', ['id' => $emailInteractionId, 'hidden' => 0]);
        $this->assertDatabaseHas('interaction_message', ['id' => $textInteractionId, 'hidden' => 0]);

        $ids = [$emailInteractionId, $textInteractionId];

        $response = $this->json(
            'POST',
            '/api/leads/interaction-message/bulk',
            ['ids' => $ids, 'hidden' => 1],
            ['access-token' => $userSeeder->authToken->access_token]
        );

        $this->assertUpdateResponse($response, false);

        $this->assertDatabaseHas('interaction_message', ['id' => $emailInteractionId, 'hidden' => 1]);
        $this->assertDatabaseHas('interaction_message', ['id' => $textInteractionId, 'hidden' => 1]);

        $interactionMessageSeeder->cleanUp();
        $userSeeder->cleanUp();
    }

    /**
     * @group CRM
     * @covers ::bulkUpdate
     */
    public function testBulkUpdateWithWrongAccessToken()
    {
        $userSeeder = new UserSeeder();
        $userSeeder->seed();

        $interactionMessageSeeder = new InteractionMessageSeeder($userSeeder->dealer);
        $interactionMessageSeeder->seed();

        $leadId = $interactionMessageSeeder->lead->identifier;

        $response = $this->json(
            'POST',
            '/api/leads/interaction-message/bulk',
            ['search' => ['lead_id' => $leadId], 'hidden' => 1],
            ['access-token' => 'wrong_access_token']
        );

        $response
            ->assertStatus(403)
            ->assertSee('Invalid access token.');

        $interactionMessageSeeder->cleanUp();
        $userSeeder->cleanUp();
    }
}
