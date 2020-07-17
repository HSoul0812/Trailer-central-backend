<?php

namespace Tests;

use App\Mail\InteractionEmail;
use App\Models\CRM\Leads\Lead;
use Illuminate\Support\Facades\Mail;

class InteractionsControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Test Interactions Index Works
     */
    public function testIndexActionCanBeAccessed()
    {
        $response = $this->json('GET', '/api/interactions/');

        $response->assertResponseStatus(200);
    }

    /**
     * Test That Send Email Opens Modal
     *
     * @author David A Conway Jr.
     */
    public function testSendEmailActionCanBeAccessed()
    {
        $response = $this->json('GET', '/api/interactions/', []);

        // Validate Status Code
        $response->assertResponseStatus(200);
    }

    /**
     * Test That Send Email Posts Successfully
     *
     * @author David A Conway Jr.
     */
    public function testSendEmailActionValidPost()
    {
        // Initialize Dummy Post data
        $postData = array(
            'email_to'      => 'david@trailercentral.com',
            'email_from'    => 'noreply@trailercentral.com',
            'email_reply'   => 'jaidyn@jrconway.net',
            'subject'       => 'Send Email Mock Test',
            'body'          => '<p>Some testing for the send email functionality!</p>
                                <hr />
                                <p>Doug Meadows Rolling M Trailers <br /><br />512-746-2515</p>',
            'product_id'    => '0',
            'user_id'       => '6884',
            'lead_id'       => '3125676'
        );

        Mail::fake();

        $response = $this->json('POST', '/api/interactions/send-email', $postData);

        Mail::assertSent(InteractionEmail::class);

        Mail::assertSent(InteractionEmail::class, function ($mail) use ($postData) {
            $this->assertResponseStatus(200);

            return true;
        });

        // Validate Status Code
        $response->assertResponseStatus(200);
    }

    ####### Common Mocks #######

    /**
     * Get Mock Lead Model
     *
     * @return Lead
     */
    private function getMockLeadTc() {
        // Create Mock Email Helper
        $json = json_decode(
            '{
                "lead":{
                    "identifier":"2971628",
                    "first_name":"David",
                    "last_name":"Conway",
                    "email_address":"david@trailercentral.com",
                    "zip":"54004",
                    "newsletter":"Not Subscribed",
                    "date_submitted":"10\/10\/2019 2:56 PM",
                    "comments":""
                }
            }'
        );

        return new Lead($json);
    }
}
