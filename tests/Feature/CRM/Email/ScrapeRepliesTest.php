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
use App\Services\Integration\Common\DTOs\ParsedEmail;
use App\Services\Integration\Common\DTOs\AttachmentFile;
use App\Services\Integration\Google\GoogleServiceInterface;
use App\Services\Integration\Google\GmailServiceInterface;
use Tests\TestCase;
use Faker\Factory as Faker;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Mockery;

class ScrapeRepliesTest extends TestCase
{
    // Get a Random Image
    const RANDOM_IMAGE = 'https://source.unsplash.com/random;';

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
     * @group CRM
     * @return void
     */
    public function testScrapeRepliesGmail()
    {
        // Get Dealer
        $dealer = NewDealerUser::findOrFail(self::getTestDealerId());
        $websiteId = $dealer->website->id;

        // Mark All Sales People as Deleted
        $salesIds = $this->disableSalesPeople($dealer->user_id);

        // Create Gmail Sales Person
        $salesPerson = factory(SalesPerson::class, 1)->create()->each(function ($salesperson) {
            // Make Token
            $tokens = factory(AccessToken::class, 1)->make([
                'relation_id' => $salesperson->id
            ]);
            $salesperson->googleToken()->save($tokens->first());
        })->first();

        // Create Lead
        $lead = factory(Lead::class, 1)->create([
            'website_id' => $websiteId,
            'dealer_id' => $dealer->id,
            'dealer_location_id' => 0,
            'inventory_id' => 0
        ])->first();

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
        $nosub = factory(EmailHistory::class, 2)->make([
            'lead_id' => $lead->identifier,
            'to_email' => $lead->email_address,
            'to_name' => $lead->full_name,
            'from_email' => $salesPerson->email,
            'from_name' => $salesPerson->full_name,
            'subject' => ''
        ]);
        $noto = factory(EmailHistory::class, 2)->make([
            'lead_id' => $lead->identifier,
            'to_email' => '',
            'to_name' => $lead->full_name,
            'from_email' => $salesPerson->email,
            'from_name' => $salesPerson->full_name
        ]);
        $noid = factory(EmailHistory::class, 2)->make([
            'lead_id' => $lead->identifier,
            'to_email' => $lead->email_address,
            'to_name' => $lead->full_name,
            'from_email' => $salesPerson->email,
            'from_name' => $salesPerson->full_name,
            'message_id' => ''
        ]);
        $unused = factory(EmailHistory::class, 5)->make();

        // Get Messages
        $messages = [];
        $parsed = [];
        $id = 0;
        foreach($replies as $reply) {
            $messages[] = $id;
            $parsed[] = $this->getParsedEmail($id, $reply);
            $id++;
        }
        foreach($nosub as $reply) {
            $messages[] = $id;
            $parsed[] = $this->getParsedEmail($id, $reply);
            $id++;
        }
        foreach($noto as $reply) {
            $messages[] = $id;
            $parsed[] = $this->getParsedEmail($id, $reply);
            $id++;
        }
        foreach($noid as $reply) {
            $messages[] = $id;
            $parsed[] = $this->getParsedEmail($id, $reply);
            $id++;
        }
        foreach($unused as $reply) {
            $messages[] = $id;
            $parsed[] = $this->getParsedEmail($id, $reply);
            $id++;
        }


        // Mock Gmail Service
        $this->mock(GoogleServiceInterface::class, function ($mock) use($salesPerson) {
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
                    'new_token' => []
                 ]);
        });

        // Mock Gmail Service
        $this->mock(GmailServiceInterface::class, function ($mock) use($folders, $messages, $parsed) {
            // Should Receive Messages With Args Once Per Folder!
            $mock->shouldReceive('messages')
                 ->times(count($folders))
                 ->andReturn($messages);

            // Mock Messages
            foreach($parsed as $k => $message) {
                // Should Receive Full Message Details Once Per Folder Per Message!
                $mock->shouldReceive('message')
                     ->withArgs([$k])
                     ->times(count($folders))
                     ->andReturn($message);
            }
        });

        // Call Leads Assign Command
        $this->artisan('email:scrape-replies 0 0 ' . self::getTestDealerId())->assertExitCode(0);

        // Mock Saved Replies
        foreach($replies as $reply) {
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
        }

        // Mock Skipping Entirely
        foreach($nosub as $email) {
            // Assert a lead status entry was saved...
            $this->assertDatabaseMissing('crm_email_processed', [
                'user_id' => $salesPerson->user_id,
                'message_id' => $email->message_id
            ]);
        }
        foreach($noto as $email) {
            // Assert a lead status entry was saved...
            $this->assertDatabaseMissing('crm_email_processed', [
                'user_id' => $salesPerson->user_id,
                'message_id' => $email->message_id
            ]);
        }
        foreach($noid as $email) {
            // Assert a lead status entry was saved...
            $this->assertDatabaseMissing('crm_email_processed', [
                'user_id' => $salesPerson->user_id,
                'message_id' => $email->message_id
            ]);
        }


        // Restore Existing Sales People
        $this->restoreSalesPeople($salesIds);

        // Delete Sales Person
        $salesPerson->delete();
    }

    /**
     * Test Scraping IMAP Emails
     *
     * @group CRM
     * @return void
     */
    public function testScrapeRepliesImap()
    {
        // Get Dealer
        $dealer = NewDealerUser::findOrFail(self::getTestDealerId());
        $websiteId = $dealer->website->id;

        // Mark All Sales People as Deleted
        $salesIds = $this->disableSalesPeople($dealer->user_id);

        // Create Gmail Sales Person
        $salesPerson = factory(SalesPerson::class, 1)->create()->first();

        // Create Lead
        $lead = factory(Lead::class, 1)->create([
            'website_id' => $websiteId,
            'dealer_id' => $dealer->id,
            'dealer_location_id' => 0,
            'inventory_id' => 0
        ])->first();

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
        $nosub = factory(EmailHistory::class, 2)->make([
            'lead_id' => $lead->identifier,
            'to_email' => $lead->email_address,
            'to_name' => $lead->full_name,
            'from_email' => $salesPerson->email,
            'from_name' => $salesPerson->full_name,
            'subject' => ''
        ]);
        $noto = factory(EmailHistory::class, 2)->make([
            'lead_id' => $lead->identifier,
            'to_email' => '',
            'to_name' => '',
            'from_email' => $salesPerson->email,
            'from_name' => $salesPerson->full_name
        ]);
        $noid = factory(EmailHistory::class, 2)->make([
            'lead_id' => $lead->identifier,
            'to_email' => $lead->email_address,
            'to_name' => $lead->full_name,
            'from_email' => $salesPerson->email,
            'from_name' => $salesPerson->full_name,
            'message_id' => ''
        ]);
        $unused = factory(EmailHistory::class, 5)->make();

        // Get Messages
        $full = [];
        $parsed = [];
        $messages = [];
        $id = 0;
        foreach($replies as $reply) {
            $messages[] = $id;
            $parsed[$id] = $this->getParsedEmail($id, $reply);
            $full[$id] = true;
            $id++;
        }
        foreach($nosub as $reply) {
            $messages[] = $id;
            $parsed[$id] = $this->getParsedEmail($id, $reply);
            $full[$id] = true;
            $id++;
        }
        foreach($noto as $reply) {
            $messages[] = $id;
            $parsed[$id] = $this->getParsedEmail($id, $reply);
            $full[$id] = true;
            $id++;
        }
        foreach($noid as $reply) {
            $messages[] = $id;
            $parsed[$id] = $this->getParsedEmail($id, $reply);
            $id++;
        }
        foreach($unused as $reply) {
            $messages[] = $id;
            $parsed[$id] = $this->getParsedEmail($id, $reply);
            $id++;
        }


        // Mock Imap Service
        $this->mock(ImapServiceInterface::class, function ($mock) use($folders, $messages, $parsed, $replies, $full) {
            // Should Receive Messages With Args Once Per Folder!
            $mock->shouldReceive('messages')
                 ->times(count($folders))
                 ->andReturn($messages);

            // Mock Replies
            foreach($parsed as $id => $email) {
                // Should Receive Overview Details Once Per Folder Per Reply!
                $mock->shouldReceive('overview')
                     ->withArgs([$id])
                     ->times(count($folders))
                     ->andReturn($email);

                // Actually Imported as Reply?
                if(isset($replies[$id])) {
                    // Should Receive Full Details Once
                    $mock->shouldReceive('full')
                         ->with(Mockery::on(function($overview) use($email) {
                            return ($overview->getMessageId() == $email->getMessageId());
                         }))
                         ->once()
                         ->andReturn($email);
                } elseif(isset($full[$id])) {
                    // Should Receive Full Details Once Per Folder
                    $mock->shouldReceive('full')
                         ->with(Mockery::on(function($overview) use($email) {
                            return ($overview->getMessageId() == $email->getMessageId());
                         }))
                         ->times(count($folders))
                         ->andReturn($email);
                } else {
                    // Should NOT Receive Full Details; This One Is Invalid and Skipped
                    $mock->shouldReceive('full')
                         ->with(Mockery::on(function($overview) use($email) {
                            return ($overview->getMessageId() == $email->getMessageId());
                         }))
                         ->never();
                }
            }
        });


        // Call Leads Assign Command
        $this->artisan('email:scrape-replies 0 0 ' . self::getTestDealerId())->assertExitCode(0);

        // Mock Saved Replies
        foreach($replies as $reply) {
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
        }

        // Mock Skipping Entirely
        foreach($nosub as $email) {
            // Assert a lead status entry was saved...
            $this->assertDatabaseMissing('crm_email_processed', [
                'user_id' => $salesPerson->user_id,
                'message_id' => $email->message_id
            ]);
        }
        foreach($noto as $email) {
            // Assert a lead status entry was saved...
            $this->assertDatabaseMissing('crm_email_processed', [
                'user_id' => $salesPerson->user_id,
                'message_id' => $email->message_id
            ]);
        }
        foreach($noid as $email) {
            // Assert a lead status entry was saved...
            $this->assertDatabaseMissing('crm_email_processed', [
                'user_id' => $salesPerson->user_id,
                'message_id' => $email->message_id
            ]);
        }

        
        // Restore Existing Sales People
        $this->restoreSalesPeople($salesIds);

        // Delete Sales Person
        $salesPerson->delete();
    }

    /**
     * Test Scraping Attachment Emails From Gmail
     *
     * @group CRM
     * @return void
     */
    public function testScrapeAttachmentsGmail()
    {
        // Get Dealer
        $dealer = NewDealerUser::findOrFail(self::getTestDealerId());
        $websiteId = $dealer->website->id;

        // Mark All Sales People as Deleted
        $salesIds = $this->disableSalesPeople($dealer->user_id);

        // Create Gmail Sales Person
        $salesPerson = factory(SalesPerson::class, 1)->create()->each(function ($salesperson) {
            // Make Token
            $tokens = factory(AccessToken::class, 1)->make([
                'relation_id' => $salesperson->id
            ]);
            $salesperson->googleToken()->save($tokens->first());
        })->first();

        // Create Lead
        $lead = factory(Lead::class, 1)->create([
            'website_id' => $websiteId,
            'dealer_id' => $dealer->id,
            'dealer_location_id' => 0,
            'inventory_id' => 0
        ])->first();

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

        // Always Skipped
        $nosub = factory(EmailHistory::class, 2)->make([
            'lead_id' => $lead->identifier,
            'to_email' => $lead->email_address,
            'to_name' => $lead->full_name,
            'from_email' => $salesPerson->email,
            'from_name' => $salesPerson->full_name,
            'subject' => ''
        ]);

        // Get Messages
        $messages = [];
        $parsed = [];
        foreach($replies as $reply) {
            // Generate Attachments?!
            $id = count($messages);
            $attachments = null;
            if($id == 1) {
                $attachments = $this->getAttachmentFiles(2, 2);
            } elseif($id == 3) {
                $attachments = $this->getAttachmentFiles(1, 1);
            }

            // Parse Email Message
            $messages[] = $id;
            $parsed[] = $this->getParsedEmail($id, $reply, $attachments);
        }
        foreach($nosub as $reply) {
            // Generate Attachments?!
            $id = count($messages);
            $attachments = $this->getAttachmentFiles(1, 1);

            // Parse Email Message
            $messages[] = $id;
            $parsed[] = $this->getParsedEmail($id, $reply, $attachments);
        }


        // Mock Gmail Service
        $this->mock(GoogleServiceInterface::class, function ($mock) use($salesPerson) {
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
                    'new_token' => []
                 ]);
        });

        // Mock Gmail Service
        $this->mock(GmailServiceInterface::class, function ($mock) use($folders, $messages, $parsed) {
            // Should Receive Messages With Args Once Per Folder!
            $mock->shouldReceive('messages')
                 ->times(count($folders))
                 ->andReturn($messages);

            // Mock Messages
            foreach($parsed as $k => $message) {
                // Should Receive Full Message Details Once Per Folder Per Message!
                $mock->shouldReceive('message')
                     ->withArgs([$k])
                     ->times(count($folders))
                     ->andReturn($message);
            }
        });

        // Fake Storage
        Storage::fake('s3email');

        // Call Leads Assign Command
        $this->artisan('email:scrape-replies 0 0 ' . self::getTestDealerId())->assertExitCode(0);

        // Mock Saved Replies
        foreach($parsed as $reply) {
            // Skipped
            if(empty($reply->getSubject())) {
                // Assert a lead status entry was NOT saved...
                $this->assertDatabaseMissing('crm_email_history', [
                    'message_id' => $reply->getMessageId()
                ]);

                // Attachment Exists in DB
                $this->assertDatabaseMissing('crm_email_attachments', [
                    'message_id' => $reply->getMessageId()
                ]);

                // Attachments ALWAYS Set
                foreach($reply->getAttachments() as $attachment) {
                    // Attachment Was Deleted From Tmp Directory
                    $this->assertFileDoesNotExist($attachment->getTmpName());
                }
            } else {
                // Assert a lead status entry was saved...
                $this->assertDatabaseHas('crm_email_history', [
                    'message_id' => $reply->getMessageId()
                ]);

                // Check Attachments
                if(!empty($reply->getAttachments())) {
                    foreach($reply->getAttachments() as $attachment) {
                        // Attachment Exists in DB
                        $this->assertDatabaseHas('crm_email_attachments', [
                            'message_id' => $reply->getMessageId(),
                            'original_filename' => $attachment->getFileName()
                        ]);

                        // Attachment Was Deleted From Tmp Directory
                        $this->assertFileDoesNotExist($attachment->getTmpName());
                    }
                } else {
                    // No Attachments
                    $this->assertDatabaseMissing('crm_email_attachments', [
                        'message_id' => $reply->getMessageId()
                    ]);
                }
            }
        }


        // Restore Existing Sales People
        $this->restoreSalesPeople($salesIds);

        // Delete Sales Person
        $salesPerson->delete();
    }

    /**
     * Test Scraping Attachment Emails From IMAP
     *
     * @group CRM
     * @return void
     */
    public function testScrapeAttachmentsImap()
    {
        // Get Dealer
        $dealer = NewDealerUser::findOrFail(self::getTestDealerId());
        $websiteId = $dealer->website->id;

        // Mark All Sales People as Deleted
        $salesIds = $this->disableSalesPeople($dealer->user_id);

        // Create Gmail Sales Person
        $salesPerson = factory(SalesPerson::class, 1)->create()->first();

        // Create Lead
        $lead = factory(Lead::class, 1)->create([
            'website_id' => $websiteId,
            'dealer_id' => $dealer->id,
            'dealer_location_id' => 0,
            'inventory_id' => 0
        ])->first();

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

        // Always Skipped
        $nosub = factory(EmailHistory::class, 2)->make([
            'lead_id' => $lead->identifier,
            'to_email' => $lead->email_address,
            'to_name' => $lead->full_name,
            'from_email' => $salesPerson->email,
            'from_name' => $salesPerson->full_name,
            'subject' => ''
        ]);

        // Get Messages
        $messages = [];
        $parsed = [];
        foreach($replies as $reply) {
            // Generate Attachments?!
            $id = count($messages);
            $attachments = null;
            if($id == 1) {
                $attachments = $this->getAttachmentFiles(2, 2);
            } elseif($id == 3) {
                $attachments = $this->getAttachmentFiles(1, 1);
            }

            // Parse Email Message
            $messages[] = $id;
            $parsed[] = $this->getParsedEmail($id, $reply, $attachments);
        }
        foreach($nosub as $reply) {
            // Generate Attachments?!
            $id = count($messages);
            $attachments = $this->getAttachmentFiles(1, 1);

            // Parse Email Message
            $messages[] = $id;
            $parsed[] = $this->getParsedEmail($id, $reply, $attachments);
        }


        // Mock Imap Service
        $this->mock(ImapServiceInterface::class, function ($mock) use($folders, $messages, $parsed, $replies) {
            // Should Receive Messages With Args Once Per Folder!
            $mock->shouldReceive('messages')
                 ->times(count($folders))
                 ->andReturn($messages);

            // Mock Messages
            foreach($parsed as $k => $message) {
                // Should Receive Full Message Details Once Per Folder Per Message!
                $mock->shouldReceive('overview')
                     ->withArgs([$k])
                     ->times(count($folders))
                     ->andReturn($message);

                // Actually Imported as Reply?
                if(isset($replies[$k])) {
                    // Should Receive Full Details Once
                    $mock->shouldReceive('full')
                         ->with(Mockery::on(function($overview) use($message) {
                            return ($overview->getMessageId() == $message->getMessageId());
                         }))
                         ->once()
                         ->andReturn($message);
                } else {
                    // Should Receive Full Details Once Per Folder
                    $mock->shouldReceive('full')
                         ->with(Mockery::on(function($overview) use($message) {
                            return ($overview->getMessageId() == $message->getMessageId());
                         }))
                         ->times(count($folders))
                         ->andReturn($message);
                }
            }
        });

        // Fake Storage
        Storage::fake('s3email');

        // Call Leads Assign Command
        $this->artisan('email:scrape-replies 0 0 ' . self::getTestDealerId())->assertExitCode(0);

        // Mock Saved Replies
        foreach($parsed as $reply) {
            // Skipped
            if(empty($reply->getSubject())) {
                // Assert a lead status entry was NOT saved...
                $this->assertDatabaseMissing('crm_email_history', [
                    'message_id' => $reply->getMessageId()
                ]);

                // Attachment Exists in DB
                $this->assertDatabaseMissing('crm_email_attachments', [
                    'message_id' => $reply->getMessageId()
                ]);

                // Attachments ALWAYS Set
                foreach($reply->getAttachments() as $attachment) {
                    // Attachment Was Deleted From Tmp Directory
                    $this->assertFileDoesNotExist($attachment->getTmpName());
                }
            } else {
                // Assert a lead status entry was saved...
                $this->assertDatabaseHas('crm_email_history', [
                    'message_id' => $reply->getMessageId()
                ]);

                // Check Attachments
                if(!empty($reply->getAttachments())) {
                    foreach($reply->getAttachments() as $attachment) {
                        // Attachment Exists in DB
                        $this->assertDatabaseHas('crm_email_attachments', [
                            'message_id' => $reply->getMessageId(),
                            'original_filename' => $attachment->getFileName()
                        ]);

                        // Attachment Was Deleted From Tmp Directory
                        $this->assertFileDoesNotExist($attachment->getTmpName());
                    }
                } else {
                    // No Attachments
                    $this->assertDatabaseMissing('crm_email_attachments', [
                        'message_id' => $reply->getMessageId()
                    ]);
                }
            }
        }


        // Restore Existing Sales People
        $this->restoreSalesPeople($salesIds);

        // Delete Sales Person
        $salesPerson->delete();
    }


    /**
     * Delete Sales People
     * 
     * @group CRM
     * @return Collection<SalesPerson>
     */
    private function disableSalesPeople($userId) {
        // Get Sales People
        $salespeople = SalesPerson::where('user_id', $userId);

        // Get Sales People ID's
        $salesIds = [];
        foreach($salespeople->get() as $person) {
            $salesIds[] = $person->id;
        }

        // Delete All
        $salespeople->delete();

        // Return People ID's
        return $salesIds;
    }

    /**
     * Restore Sales People
     * 
     * @group CRM
     * @return Collection<SalesPerson>
     */
    private function restoreSalesPeople($salesIds) {
        // Loop Sales People
        $salespeople = [];
        foreach($salesIds as $salesId) {
            $salespeople[] = SalesPerson::withTrashed()->find($salesId)->restore();
        }

        // Return
        return collect($salespeople);
    }


    /**
     * Get Parsed Email
     * 
     * @group CRM
     * @param string $id
     * @param EmailHistory $email
     * @return ParsedEmail
     */
    private function getParsedEmail($id, $email, $attachments = null) {
        // Create Parsed Email
        $parsed = new ParsedEmail();
        $parsed->setId((string) $id);

        // Set Lead ID
        $parsed->setLeadId($email->lead_id);

        // Set Message ID
        $parsed->setMessageId($email->message_id);

        // Set To/From
        $parsed->setToName($email->to_name);
        $parsed->setToEmail($email->to_email);
        $parsed->setFromName($email->from_name);
        $parsed->setFromEmail($email->from_email);

        // Set Subject/Body
        $parsed->setSubject($email->subject);
        $parsed->setBody($email->body);

        // Add Attachments
        if(!empty($attachments)) {
            $parsed->setAttachments($attachments);
        }

        // Set Date
        $parsed->setDate($email->date_sent->format('Y-m-d H:i:s'));

        // Return ParsedEmail
        return $parsed;
    }

    /**
     * Generate Fake Attachment Files
     * 
     * @group CRM
     * @param int $min
     * @param int $max
     * @return Collection<AttachmentFile>
     */
    private function getAttachmentFiles($min = 5, $max = 10) {
        // Calculate Total Number
        $attachments = [];
        $total = rand($min, $max);

        // Initialize Faker
        $faker = Faker::create();

        // Loop Total
        for($i = 0; $i < $total; $i++) {
            // Create Image
            $contents = file_get_contents(self::RANDOM_IMAGE);

            // Write File Locally
            $filename = $faker->md5 . '.jpg';
            $filepath = $_ENV['MAIL_ATTACHMENT_DIR'] . '/' . $filename;
            file_put_contents($filepath, $contents);

            // Create Parsed Email
            $attachment = new AttachmentFile();

            // Set Temp Filename
            $attachment->setTmpName($filepath);

            // Set File Path
            $attachment->setFilePath($faker->imageUrl);

            // Set Filename
            $attachment->setFileName($filename);

            // Add Attachment to Array
            $attachments[] = $attachment;
        }

        // Return Collection<AttachmentFile>
        return new Collection($attachments);
    }
}
