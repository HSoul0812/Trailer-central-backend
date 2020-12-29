<?php

declare(strict_types=1);

namespace Tests\Feature\CRM\Email;

use App\Models\CRM\Interactions\EmailHistory;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\User\SalesPerson;
use App\Models\CRM\User\EmailFolder;
use App\Models\Integration\Auth\AccessToken;
use App\Models\User\NewDealerUser;
use App\Services\CRM\Email\ImapServiceInterface;
use App\Services\Integration\Google\GoogleServiceInterface;
use App\Services\Integration\Google\GmailServiceInterface;
use Tests\TestCase;
use Mockery;

class ScrapeRepliesTest extends TestCase
{

    /**
     * Set Up Test
     */
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Test Scraping Gmail Emails
     *
     * @return void
     */
    public function testScrapeRepliesGmail()
    {
        // Get Dealer
        $dealer = NewDealerUser::findOrFail(self::getTestDealerId());

        // Mark All Sales People as Deleted
        $salespeople = $this->disableSalesPeople($dealer->user_id);

        // Create Gmail Sales Person
        $salesPerson = factory(SalesPerson::class, 1)->create()->each(function ($salesperson) {
            // Make Token
            $tokens = factory(AccessToken::class, 1)->make([
                'relation_id' => $salesperson->id
            ]);
            $salesperson->googleToken()->save($tokens->first());
        })->first();

        // Create Lead
        $lead = factory(Lead::class, 1)->create()->first();

        // Get Folders
        $folders = EmailFolder::getDefaultGmailFolders();

        // Create Dummy Emails
        $replies = factory(EmailHistory::class, 5)->make([
            'lead_id' => $lead->identifier,
            'to_email' => $lead->email_address,
            'to_name' => $lead->full_name,
            'from_email' => $salesPerson->email,
            'from_name' => $salesPerson->full_name
        ]);
        $unused = factory(EmailHistory::class, 5)->make();

        // Get Messages
        $messages = [];
        foreach($replies as $reply) {
            $msg = new \stdclass;
            $msg->id = $reply->message_id;
            $msg->reply = $reply;
            $messages[] = $msg;
        }
        foreach($unused as $reply) {
            $msg = new \stdclass;
            $msg->id = $reply->message_id;
            $msg->reply = $reply;
            $messages[] = $msg;
        }


        // Mock Gmail Service
        $this->mock(GoogleServiceInterface::class, function ($mock) use($folders, $salesPerson) {
            // Should Receive Messages With Args Once Per Folder!
            $mock->shouldReceive('validate')
                 ->with(Mockery::on(function($accessToken) use($salesPerson) {
                    if($salesPerson->id == $accessToken->relation_id) {
                        return true;
                    }
                    return false;
                 }))
                 ->once()
                 ->andReturn([
                    'is_valid' => true,
                    'is_expired' => false,
                    'new_token' => array()
                 ]);
        });

        // Mock Gmail Service
        $this->mock(GmailServiceInterface::class, function ($mock) use($salesPerson, $folders, $messages) {
            // Loop Folders
            foreach($folders as $folder) {
                // Should Receive Messages With Args Once Per Folder!
                $mock->shouldReceive('messages')
                     ->withArgs(Mockery::on(function($accessToken, $label) use($salesPerson, $folder) {
                        if($salesPerson->id == $accessToken->relation_id && $label == $folder->name) {
                            return true;
                        }
                        return false;
                     }))
                     ->once()
                     ->andReturn($messages);

                // Mock Messages
                foreach($messages as $message) {
                    // Should Receive Full Message Details Once Per Folder Per Message!
                    $mock->shouldReceive('message')
                         ->with(Mockery::on(function($item) use($message) {
                            return ($item->id == $message->id);
                         }))
                         ->once()
                         ->andReturn([
                            'message_id' => $message->reply->message_id,
                            'to_email' => $message->reply->to_email,
                            'to_name' => $message->reply->to_name,
                            'from_email' => $message->reply->from_email,
                            'from_name' => $message->reply->from_name,
                            'subject' => $message->reply->subject,
                            'body' => $message->reply->body,
                            'is_html' => !empty($message->reply->is_html),
                            'attachments' => array(),
                            'date_sent' => $message->reply->date_sent
                         ]);
                }
            }
        });

        // Call Leads Assign Command
        $this->artisan('email:scrape-replies 0 0 ' . self::getTestDealerId())->assertExitCode(0);

        // Mock Saved Replies
        /*foreach($replies as $reply) {
            // Assert a lead status entry was saved...
            $this->assertDatabaseHas('crm_email_history', [
                'message_id' => $reply->message_id
            ]);
        }

        // Mock Skipped Replies
        foreach($unused as $email) {
            // Assert a lead status entry was saved...
            $this->assertDatabaseHas('crm_email_processed', [
                'user_id' => $salesPerson->user_id,
                'message_id' => $email->message_id
            ]);

            // Assert a lead status entry was saved...
            $this->assertDatabaseMissing('crm_email_history', [
                'message_id' => $email->message_id
            ]);
        }*/


        // Restore Existing Sales People
        $this->restoreSalesPeople($salespeople);

        // Delete Sales Person
        $salesPerson->delete();
    }

    /**
     * Test Scraping IMAP Emails
     *
     * @return void
     */
    public function testScrapeRepliesImap()
    {
        // Get Dealer
        $dealer = NewDealerUser::findOrFail(self::getTestDealerId());

        // Mark All Sales People as Deleted
        $salespeople = $this->disableSalesPeople($dealer->user_id);

        // Create Gmail Sales Person
        $salesPerson = factory(SalesPerson::class, 1)->create()->first();

        // Create Lead
        $lead = factory(Lead::class, 1)->create()->first();

        // Get Folders
        $folders = EmailFolder::getDefaultFolders();

        // Create Dummy Emails
        $replies = factory(EmailHistory::class, 5)->make([
            'lead_id' => $lead->identifier,
            'to_email' => $lead->email_address,
            'to_name' => $lead->full_name,
            'from_email' => $salesPerson->email,
            'from_name' => $salesPerson->full_name
        ]);
        $unused = factory(EmailHistory::class, 5)->make();

        // Get Messages
        $messages = [];
        foreach($replies as $reply) {
            $messages[] = count($messages);
        }
        foreach($unused as $reply) {
            $messages[] = count($messages);
        }


        // Mock Imap Service
        $this->mock(ImapServiceInterface::class, function ($mock) use($salesPerson, $folders, $messages, $replies, $unused) {
            // Loop Folders
            foreach($folders as $folder) {
                // Should Receive Messages With Args Once Per Folder!
                $mock->shouldReceive('messages')
                     ->with(Mockery::on(function($sales, $emailFolder) use($salesPerson, $folder) {
                        dd($emailFolder);
                        if($sales->id == $salesPerson->id && $emailFolder->name == $folder->name) {
                            return true;
                        }
                        return false;
                     }))
                     ->once()
                     ->andReturn($messages);

                // Mock Replies
                foreach($replies as $reply) {
                    // Should Receive Overview Details Once Per Folder Per Reply!
                    $overview = [
                        'references' => array(),
                        'message_id' => $reply->message_id,
                        'root_message_id' => $reply->message_id,
                        'uid' => $reply->message_id,
                        'to_email' => $reply->to_email,
                        'to_name' => $reply->to_name,
                        'from_email' => $reply->from_email,
                        'from_name' => $reply->from_name,
                        'subject' => $reply->subject,
                        'date_sent' => $reply->date_sent
                    ];
                    $mock->shouldReceive('overview')
                         ->with(Mockery::on(function($messageId) use($reply) {
                           return ($messageId == $reply->message_id);
                         }))
                         ->once()
                         ->andReturn($overview);

                    // Should Receive Full Details Once Per Folder Per Reply!
                    $parsed = $overview;
                    $parsed['body'] = $reply->body;
                    $parsed['is_html'] = $reply->is_html;
                    $parsed['attachments'] = array();
                    $mock->shouldReceive('parsed')
                         ->with(Mockery::on(function($messageId) use($reply) {
                           return ($messageId == $reply->message_id);
                         }))
                         ->once()
                         ->andReturn($parsed);
                }

                // Mock Unused Emails
                foreach($unused as $reply) {
                    // Should Receive Overview Details Once Per Folder Per Reply!
                    $mock->shouldReceive('overview')
                         ->with(Mockery::on(function($messageId) use($reply) {
                           return ($messageId == $reply->message_id);
                         }))
                         ->once()
                         ->andReturn([
                            'references' => array(),
                            'message_id' => $reply->message_id,
                            'root_message_id' => $reply->message_id,
                            'uid' => $reply->message_id,
                            'to_email' => $reply->to_email,
                            'to_name' => $reply->to_name,
                            'from_email' => $reply->from_email,
                            'from_name' => $reply->from_name,
                            'subject' => $reply->subject,
                            'date_sent' => $reply->date_sent
                    ]);

                    // Should NOT Receive Full Details; This One Is Invalid and Skipped
                    $mock->shouldReceive('parsed')
                         ->with(Mockery::on(function($messageId) use($reply) {
                           return ($messageId == $reply->message_id);
                         }))
                         ->never();
                }
            }
        });


        // Call Leads Assign Command
        $this->artisan('email:scrape-replies 0 0 ' . self::getTestDealerId())->assertExitCode(0);

        // Mock Saved Replies
        /*foreach($replies as $reply) {
            // Assert a lead status entry was saved...
            $this->assertDatabaseHas('crm_email_history', [
                'message_id' => $reply->message_id
            ]);
        }

        // Mock Skipped Replies
        foreach($unused as $email) {
            // Assert a lead status entry was saved...
            $this->assertDatabaseHas('crm_email_processed', [
                'user_id' => $salesPerson->user_id,
                'message_id' => $email->message_id
            ]);

            // Assert a lead status entry was saved...
            $this->assertDatabaseMissing('crm_email_history', [
                'message_id' => $email->message_id
            ]);
        }*/

        
        // Restore Existing Sales People
        $this->restoreSalesPeople($salespeople);

        // Delete Sales Person
        $salesPerson->delete();
    }


    /**
     * Delete Sales People
     * 
     * @return Collection<SalesPerson>
     */
    private function disableSalesPeople($userId) {
        // Get Sales People
        $salespeople = SalesPerson::where('user_id', $userId);

        // Delete All
        $salespeople->delete();

        // Return
        return $salespeople;
    }

    /**
     * Restore Sales People
     * 
     * @return Collection<SalesPerson>
     */
    private function restoreSalesPeople($salespeople) {
        // Loop Sales People
        foreach($salespeople as $salesperson) {
            $salesperson->restore();
        }

        // Return
        return $salespeople;
    }
}
