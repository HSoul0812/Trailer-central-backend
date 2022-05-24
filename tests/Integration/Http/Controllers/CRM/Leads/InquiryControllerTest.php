<?php

namespace Tests\Integration\Http\Controllers\CRM\Leads;

use Tests\Integration\IntegrationTestCase;

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
}
