<?php

namespace Tests\Integration\Http\Controllers\CRM\Leads;

use App\Models\CRM\Leads\LeadType;
use Tests\Integration\IntegrationTestCase;

/**
 * Class LeadTypeControllerTest
 * @package Tests\Integration\Http\Controllers\CRM\Leads
 *
 * @coversDefaultClass \App\Http\Controllers\v1\CRM\Leads\LeadTypeController
 */
class LeadTypeControllerTest extends IntegrationTestCase
{
    /**
     * @covers ::publicTypes
     * @group CRM
     */
    public function testPublicStatuses()
    {
        $response = $this->json(
            'GET',
            '/api/leads/types/public'
        );

        $leadsStatuses = [];

        foreach (LeadType::PUBLIC_TYPES as $index => $typeName) {
            $leadsStatuses[] = [
                'id' => $index,
                'name' => $typeName,
            ];
        }

        $this->assertResponseDataEquals($response, $leadsStatuses, false);
    }
}
