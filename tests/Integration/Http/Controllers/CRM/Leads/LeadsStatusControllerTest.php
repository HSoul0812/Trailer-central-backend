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

    /**
     * @covers ::publicStatuses
     * @group CRM
     */
    public function testPublicStatuses()
    {
        $response = $this->json(
            'GET',
            '/api/leads/status/public'
        );

        $leadsStatuses = [];

        foreach (LeadStatus::PUBLIC_STATUSES as $index => $statusName) {
            $leadsStatuses[] = [
               'id' => $index,
               'name' => $statusName,
            ];
        }

        $this->assertResponseDataEquals($response, $leadsStatuses, false);
    }

    public function updateStatusDataProvider()
    {
        return [
            'Closed Time Not Null if Closed status' => [
                LeadStatus::STATUS_WON,
                false
            ],
            'Closed Time Not Null if Closed (Won) status' => [
                LeadStatus::STATUS_WON_CLOSED,
                false
            ],
            'Closed Time Not Null if Closed (Lost) status' => [
                LeadStatus::STATUS_LOST,
                false
            ],
            'Closed Time IS Null if Non-Closed status' => [
                LeadStatus::STATUS_HOT,
                true
            ],
        ];
    }

    /**
     * @dataProvider updateStatusDataProvider
     * @covers ::publicStatuses
     * @group CRM
     */
    public function testUpdateClosedStatus($statusString, $closedTimeIsNull)
    {
        $statusSeeder = new StatusSeeder();
        $statusSeeder->seed();

        $createdStatus = $statusSeeder->createdStatus;
        $status = reset($createdStatus);

        $params = [
            'id' => $status->id,
            'status' => $statusString,
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

        $params['closed_at'] = null;

        if ($closedTimeIsNull) {

            $this->assertDatabaseHas('crm_tc_lead_status', array_merge(
                ['id' => $id], $params
            ));
        } else {

            $this->assertDatabaseMissing('crm_tc_lead_status', array_merge(
                ['id' => $id], $params
            ));
        }

        $statusSeeder->cleanUp();
    }
}
