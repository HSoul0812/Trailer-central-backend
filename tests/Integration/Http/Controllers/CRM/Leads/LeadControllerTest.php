<?php

namespace Tests\Integration\Http\Controllers\CRM\Leads;

use App\Models\CRM\Leads\Lead;
use App\Models\User\User;
use App\Models\User\AuthToken;
use Tests\Integration\IntegrationTestCase;
use App\Models\Website\Website;

/**
 * Class LeadControllerTest
 * @package Tests\Integration\Http\Controllers\CRM\Leads
 *
 * @coversDefaultClass \App\Http\Controllers\v1\CRM\Leads\LeadController
 */
class LeadControllerTest extends IntegrationTestCase
{
    /** @var DealerUser */
    protected $dealer;

    /** @var Lead */
    protected $lead;

    /** @var AuthToken */
    protected $token;

    /** @var Website */
    protected $website;

    public function setUp(): void
    {
        parent::setUp();

        $this->dealer = factory(User::class)->create([
            'type' => User::TYPE_DEALER,
            'state' => User::STATUS_ACTIVE
        ]);

        $this->token = factory(AuthToken::class)->create([
            'user_id' => $this->dealer->getKey(),
            'user_type' => AuthToken::USER_TYPE_DEALER,
        ]);

        $this->website = factory(Website::class)->create([
            'dealer_id' => $this->dealer->getKey()
        ]);

        $this->lead = factory(Lead::class)->create([
            'dealer_id' => $this->dealer->getKey(),
            'website_id' => $this->website->getKey()
        ]);
    }

    /**
     * @group CRM
     * @covers ::output
     */
    public function testOutput()
    {
        $params = [
            'archived' => 0,
            'dealer_id' => $this->dealer->getKey()
        ];

        $response = $this->json(
            'GET',
            '/api/leads/output',
            $params,
            ['access-token' => $this->token->access_token]
        );

        $output = $response->getContent();

        $this->assertStringContainsString('Email,Phone,"Preferred Contact","First Name","Last Name","Lead Type","Lead Source",Address,City,State,Zip,Status,"Closed Date",Comments,"Submission Date"',
            $output);
        $this->assertStringContainsString($this->lead->first_name, $output);
        $this->assertStringContainsString($this->lead->last_name, $output);
        $this->assertStringContainsString($this->lead->lead_type, $output);
        $this->assertStringContainsString($this->lead->address, $output);
        $this->assertStringContainsString($this->lead->city, $output);
        $this->assertStringContainsString($this->lead->state, $output);
        $this->assertStringContainsString($this->lead->zip, $output);
    }

    public function testDelete()
    {
        $response = $this->json(
            'DELETE',
            '/api/leads/'. $this->lead->getKey(),
            [],
            ['access-token' => $this->token->access_token]
        );

        $response->assertStatus(200);

        $this->assertDatabaseMissing(Lead::getTableName(), [
            'identifier' => $this->lead->getKey()
        ]);

    }

    public function tearDwon(): void
    {
        $this->lead->delete();

        $this->website->delete();

        $this->token->delete();

        $this->dealer->delete();

        parent::tearDown();
    }
}