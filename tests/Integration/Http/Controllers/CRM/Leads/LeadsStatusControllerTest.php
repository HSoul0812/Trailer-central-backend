<?php

namespace Tests\Integration\Http\Controllers\CRM\Leads;

use App\Models\CRM\Leads\LeadStatus;
use Tests\database\seeds\CRM\Leads\StatusSeeder;
use Tests\Integration\IntegrationTestCase;

/**
 * Class LeadsStatusControllerTest
 * @package Tests\Integration\Http\Controllers\CRM\Leads
 *
 * @coversDefaultClass \App\Http\Controllers\v1\CRM\Leads\LeadStatusController
 */
class LeadsStatusControllerTest extends IntegrationTestCase
{
    /**
     * @group CRM
     * @covers ::create
     */
    public function testCreate()
    {
        $statusSeeder = new StatusSeeder();
        $statusSeeder->seed();

        $leads = $statusSeeder->leads;
        $lead = reset($leads);

        $params = [
            'tc_lead_identifier' => $lead->identifier,
            'status' => LeadStatus::STATUS_UNCONTACTED,
        ];

        $response = $this->json(
            'PUT',
            '/api/leads/status',
            $params,
            ['access-token' => $statusSeeder->authToken->access_token]
        );

        $this->assertCreateResponse($response);

        $id = $this->getResponseId($response);

        $this->assertDatabaseHas('crm_tc_lead_status', array_merge(
            ['id' => $id], $params
        ));

        $statusSeeder->cleanUp();
    }

    /**
     * @group CRM
     * @covers ::create
     */
    public function testCreateWrongAccessToken()
    {
        $statusSeeder = new StatusSeeder();
        $statusSeeder->seed();

        $leads = $statusSeeder->leads;
        $lead = reset($leads);

        $params = [
            'tc_lead_identifier' => $lead->identifier,
            'status' => LeadStatus::STATUS_UNCONTACTED,
        ];

        $response = $this->json(
            'PUT',
            '/api/leads/status',
            $params,
            ['access-token' => 'wrong_access_token']
        );

        $response
            ->assertStatus(403)
            ->assertSee('Invalid access token.');

        $statusSeeder->cleanUp();
    }

    /**
     * @group CRM
     * @covers ::update
     */
    public function testUpdate()
    {
        $statusSeeder = new StatusSeeder();
        $statusSeeder->seed();

        $createdStatus = $statusSeeder->createdStatus;
        $status = reset($createdStatus);

        $params = [
            'id' => $status->id,
            'status' => LeadStatus::STATUS_HOT,
        ];

        $response = $this->json(
            'POST',
            '/api/leads/status/' . $status->id,
            $params,
            ['access-token' => $statusSeeder->authToken->access_token]
        );

        $this->assertUpdateResponse($response);

        $id = $this->getResponseId($response);

        $this->assertDatabaseHas('crm_tc_lead_status', array_merge(
            ['id' => $id], $params
        ));
    }

    /**
     * @group CRM
     * @covers ::update
     */
    public function testUpdateWrongAccessToken()
    {
        $statusSeeder = new StatusSeeder();
        $statusSeeder->seed();

        $createdStatus = $statusSeeder->createdStatus;
        $status = reset($createdStatus);

        $params = [
            'id' => $status->id,
            'status' => LeadStatus::STATUS_HOT,
        ];

        $response = $this->json(
            'POST',
            '/api/leads/status/' . $status->id,
            $params,
            ['access-token' => 'wrong_access_token']
        );

        $response
            ->assertStatus(403)
            ->assertSee('Invalid access token.');

        $statusSeeder->cleanUp();
    }
}
