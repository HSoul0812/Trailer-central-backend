<?php

namespace Tests\Integration\Http\Controllers\CRM\Leads;

use Tests\Integration\IntegrationTestCase;
use Tests\database\seeds\CRM\Leads\InquirySeeder;
use App\Models\Website\Config\WebsiteConfig;
use App\Models\CRM\Leads\LeadType;
use App\Services\CRM\Leads\DTOs\InquiryLead;
use App\Models\CRM\Leads\Lead;

class InquiryControllerTest extends IntegrationTestCase
{
    public function testCreateChecksForBannedLeadTexts()
    {
        $params = [
            'comments' => 'your website'
        ];

        $response = $this->json(
            'PUT',
            '/api/inquiry/create',
            $params,
            ['access-token' => $this->accessToken()]
        );

        $response
            ->assertStatus(422)
            ->assertSee('Lead contains banned text.');
    }

    public function testSendChecksForBannedLeadTexts()
    {
        $params = [
            'comments' => 'a website that your organization hosts'
        ];

        $response = $this->json(
            'PUT',
            '/api/inquiry/send',
            $params,
            ['access-token' => $this->accessToken()]
        );

        $response
            ->assertStatus(422)
            ->assertSee('Lead contains banned text.');
    }

    public function testTextChecksForBannedLeadTexts()
    {
        $params = [
            'sms_message' => 'a website that your organization hosts'
        ];

        $response = $this->json(
            'PUT',
            '/api/inquiry/text',
            $params,
            ['access-token' => $this->accessToken()]
        );

        $response
            ->assertStatus(422)
            ->assertSee('Lead contains banned text.');
    }

    public function testCreateAutoMergeCaseInsensitiveName()
    {
        $seeder = new InquirySeeder;

        $seeder->seed();

        $this->assertDatabaseHas('website_config', [
            'website_id' => $seeder->website->getKey(),
            'key' => WebsiteConfig::LEADS_MERGE_ENABLED,
            'value' => 1
        ]);

        $params = [
            'website_id' => $seeder->website->getKey(),
            'inquiry_type' => InquiryLead::INQUIRY_TYPE_DEFAULT,
            'lead_types' => [LeadType::TYPE_GENERAL],
            'first_name' => strtoupper($seeder->lead->first_name),
            'last_name' => strtoupper($seeder->lead->last_name),
            'email_address' => $seeder->lead->email_address,
        ];

        $response = $this->json(
            'PUT',
            '/api/inquiry/create',
            $params,
            ['access-token' => $seeder->authToken->access_token]
        );

        $response->assertStatus(200);

        $this->assertEquals(1, Lead::where('dealer_id', $seeder->dealer->getKey())->count());

        $this->assertEquals(1, $seeder->lead->interactions()
            ->where('interaction_notes', 'like', 'Original Inquiry: '. strtoupper($seeder->lead->first_name) .' '. strtoupper($seeder->lead->last_name) .'%')->count());

        $seeder->cleanUp();
    }
}
