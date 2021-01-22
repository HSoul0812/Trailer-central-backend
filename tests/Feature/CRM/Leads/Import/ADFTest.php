<?php

declare(strict_types=1);

namespace Tests\Feature\CRM\Leads\Import;

use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\LeadImport;
use App\Models\Integration\Auth\AccessToken;
use App\Models\Inventory\Inventory;
use App\Models\User\User;
use App\Models\User\DealerLocation;
use App\Models\System\Email;
use App\Services\Integration\Common\DTOs\ParsedEmail;
use App\Services\Integration\Google\GoogleServiceInterface;
use App\Services\Integration\Google\GmailServiceInterface;
use Tests\TestCase;
use Mockery;

class ADFTest extends TestCase
{
    /**
     * App\Repositories\CRM\Leads\LeadRepositoryInterface $leads
     * App\Repositories\CRM\Leads\ImportRepositoryInterface $imports
     * App\Repositories\System\EmailRepositoryInterface $emails
     * App\Repositories\Integration\Auth\TokenRepositoryInterface $tokens
     */
    protected $leads;
    protected $imports;
    protected $emails;
    protected $tokens;

    /**
     * Set Up Test
     */
    public function setUp(): void
    {
        parent::setUp();

        // Make Repositories
        $this->leads = $this->app->make('App\Repositories\CRM\Leads\LeadRepositoryInterface');
        $this->imports = $this->app->make('App\Repositories\CRM\Leads\ImportRepositoryInterface');
        $this->emails = $this->app->make('App\Repositories\System\EmailRepositoryInterface');
        $this->tokens = $this->app->make('App\Repositories\Integration\Auth\TokenRepositoryInterface');
    }

    /**
     * Test Importing ADF Emails
     *
     * @return void
     */
    public function testADFImport()
    {
        // Get Dealer
        $dealer = User::findOrFail(self::getTestDealerId());
        $websiteId = $dealer->website->id;

        // Create Dealer Location
        $location = DealerLocation::findOrFail(self::getTestDealerLocationId());

        // Get System Email
        $systemEmail = $this->getSystemEmail();

        // Add Lead Imports
        $this->refreshLeadImports($dealer, $location);

        // Define Folders
        $inbox = config('adf.imports.gmail.inbox');
        $processed = config('adf.imports.gmail.processed');
        $invalid = config('adf.imports.gmail.invalid');

        // Create Vehicles
        $vehicles = [];
        $inventory = factory(Inventory::class, 2)->create([
            'dealer_id' => $dealer->dealer_id,
            'dealer_location_id' => $location->dealer_location_id
        ]);
        foreach($inventory as $item) {
            $vehicles[$item->inventory_id] = $item;
        }

        // Create Leads
        $leads = [];
        $noloc = [];
        foreach($vehicles as $vehicle) {
            // Create Leads for Vehicle With Location
            $leads[] = factory(Lead::class, 1)->make([
                'website_id' => $websiteId,
                'dealer_id' => $dealer->dealer_id,
                'dealer_location_id' => $location->dealer_location_id,
                'inventory_id' => $vehicle->inventory_id
            ])->first();

            // Create Leads for Vehicle With No Location
            $noloc[] = factory(Lead::class, 1)->make([
                'website_id' => $websiteId,
                'dealer_id' => $dealer->dealer_id,
                'dealer_location_id' => 0,
                'inventory_id' => $vehicle->inventory_id
            ])->first();
        }

        // Create Leads With No Inventory
        $noinv = factory(Lead::class, 2)->make([
            'website_id' => $websiteId,
            'dealer_id' => $dealer->dealer_id,
            'dealer_location_id' => 0,
            'inventory_id' => 0
        ]);

        // Create Leads To Not Be Imported By Email
        $noimport = factory(Lead::class, 2)->make([
            'website_id' => $websiteId,
            'dealer_id' => $dealer->dealer_id,
            'dealer_location_id' => 0,
            'inventory_id' => 0
        ]);

        // Create Leads To Not Be Imported By Invalid ADF
        $noadf = factory(Lead::class, 2)->make([
            'website_id' => $websiteId,
            'dealer_id' => $dealer->dealer_id,
            'dealer_location_id' => 0,
            'inventory_id' => 0
        ]);

        // Create Leads To Not Be Imported By Invalid XML
        $noxml = factory(Lead::class, 2)->make([
            'website_id' => $websiteId,
            'dealer_id' => $dealer->dealer_id,
            'dealer_location_id' => 0,
            'inventory_id' => 0
        ]);

        // Get Messages
        $messages = [];
        $parsed = [];
        $id = 0;
        foreach($leads as $lead) {
            $body = $this->getAdfXml($lead, $dealer, $location, $vehicles[$lead->inventory_id]);
            $parsed[] = $this->getParsedEmail($id, $location->email, $body);
            $messages[] = $id;
            $id++;
        }
        foreach($noloc as $lead) {
            $body = $this->getAdfXml($lead, $dealer, $location, $vehicles[$lead->inventory_id]);
            $parsed[] = $this->getParsedEmail($id, $dealer->email, $body);
            $messages[] = $id;
            $id++;
        }
        foreach($noinv as $lead) {
            $body = $this->getAdfXml($lead, $dealer, $location);
            $parsed[] = $this->getParsedEmail($id, $dealer->email, $body);
            $messages[] = $id;
            $id++;
        }
        foreach($noimport as $lead) {
            $body = $this->getAdfXml($lead, $dealer, $location);
            $parsed[] = $this->getParsedEmail($id, $lead->email_address, $body);
            $messages[] = $id;
            $id++;
        }
        foreach($noadf as $lead) {
            $body = $this->getNoAdfXml($lead, $dealer, $location);
            $parsed[] = $this->getParsedEmail($id, $lead->email_address, $body);
            $messages[] = $id;
            $id++;
        }
        foreach($noxml as $lead) {
            $parsed[] = $this->getParsedEmail($id, $lead->email_address, $lead->comments);
            $messages[] = $id;
            $id++;
        }


        // Mock Gmail Service
        $this->mock(GoogleServiceInterface::class, function ($mock) use($systemEmail) {
            // Should Receive Messages With Args Once Per Folder!
            $mock->shouldReceive('validate')
                 ->with(Mockery::on(function($accessToken) use($systemEmail) {
                    if($systemEmail->id == $accessToken->relation_id) {
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
        $this->mock(GmailServiceInterface::class, function ($mock) use($messages, $parsed, $systemEmail, $inbox, $processed, $invalid) {
            // Should Receive Messages With Args Once Per Folder!
            $mock->shouldReceive('messages')
                 ->once()
                 ->andReturn($messages);

            // Mock Messages
            foreach($parsed as $k => $message) {
                // Should Receive Full Message Details Once Per Folder Per Message!
                $mock->shouldReceive('message')
                     ->withArgs([$k])
                     ->once()
                     ->andReturn($message);

                // Should Receive Messages With Args Once Per Folder!
                $mock->shouldReceive('move')
                     ->with(Mockery::on(function($accessToken, $mailId, $new, $remove)
                                        use($systemEmail, $k, $inbox, $processed, $invalid) {
                        // System Email Matches Relation ID, Mail ID Matches Current Item, Remove is Inbox
                        if($mailId === (string) $k) {
                            return true;
                        }
                        return false;
                     }))
                     ->once()
                     ->andReturn(true);
            }
        });

        // Call Import ADF Leads
        $this->artisan('leads:import:adf')->assertExitCode(0);

        // Assert Leads Exist
        foreach($leads as $lead) {
            // Assert a lead was saved...
            $this->assertDatabaseHas('website_lead', [
                'website_id' => $websiteId,
                'dealer_id' => $lead->dealer_id,
                'dealer_location_id' => $lead->dealer_location_id,
                'inventory_id' => $lead->inventory_id,
                'first_name' => $lead->first_name,
                'last_name' => $lead->last_name,
                'email_address' => $lead->email_address,
                'phone_number' => $lead->phone_number
            ]);
        }

        // Assert Leads With No Location Exist
        foreach($noloc as $lead) {
            // Assert a lead was saved...
            $this->assertDatabaseHas('website_lead', [
                'website_id' => $websiteId,
                'dealer_id' => $lead->dealer_id,
                'dealer_location_id' => 0,
                'inventory_id' => $lead->inventory_id,
                'first_name' => $lead->first_name,
                'last_name' => $lead->last_name,
                'email_address' => $lead->email_address,
                'phone_number' => $lead->phone_number
            ]);
        }

        // Assert Leads With No Inventory Exist
        foreach($noinv as $lead) {
            // Assert a lead was saved...
            $this->assertDatabaseHas('website_lead', [
                'website_id' => $websiteId,
                'dealer_id' => $lead->dealer_id,
                'dealer_location_id' => 0,
                'inventory_id' => 0,
                'first_name' => $lead->first_name,
                'last_name' => $lead->last_name,
                'email_address' => $lead->email_address,
                'phone_number' => $lead->phone_number
            ]);
        }

        // Assert Leads Don't Exist
        foreach($noimport as $lead) {
            // Assert a lead wasn't saved...
            $this->assertDatabaseMissing('website_lead', [
                'website_id' => $websiteId,
                'dealer_id' => $lead->dealer_id,
                'dealer_location_id' => 0,
                'inventory_id' => 0,
                'first_name' => $lead->first_name,
                'last_name' => $lead->last_name,
                'email_address' => $lead->email_address,
                'phone_number' => $lead->phone_number
            ]);
        }
    }


    /**
     * Get ADF Formatted XML Data
     * 
     * @param Lead $lead
     * @param User $dealer
     * @param DealerLocation $location
     * @param Inventory || null $inventory
     * @return type
     */
    private function getAdfXml(Lead $lead, User $dealer, DealerLocation $location, $inventory = null) {
        return '<?xml version="1.0" encoding="UTF-8"?>
<?adf version="1.0"?>
<adf>
 <prospect>
  <requestdate>' . $lead->date_submitted . '</requestdate>
  <vehicle>
   <year>' . ($inventory->year ?? '') . '</year>
   <make>' . ($inventory->make ?? '') . '</make>
   <model>' . ($inventory->model ?? '') . '</model>
   <stock>' . ($inventory->stock ?? '') . '</stock>
   <vin>' . ($inventory->vin ?? '') . '</vin>
  </vehicle>
  <customer>
   <contact>
    <name part="first">' . $lead->first_name . '</name>
    <name part="last">' . $lead->last_name . '</name>
    <email>' . ($lead->email_address ?? '') . '</email>
    <phone>' . ($lead->phone_number ?? '') . '</phone>
   </contact>
   <comments><![CDATA[' . $lead->comments . ']]></comments>
   <address type="home">
    <street>' . $lead->address . '</street>
    <city>' . $lead->city . '</city>
    <regioncode>' . $lead->state . '</regioncode>
    <postalcode>' . $lead->zip . '</postalcode>
   </address>
  </customer>
  <vendor>
   <id sequence="1" source="DealerID">' . $dealer->dealer_id . '</id>
   <id sequence="2" source="DealerLocationID">' . $dealer->dealer_location_id . '</id>
   ' . ($lead->identifier ? '<id sequence="3" source="ID">' . $lead->identifier . '</id>' : '') . '
   <vendorname>' . $dealer->name . '</vendorname>
   <contact>
    <name part="full">' . $location->contact . '</name>
    <url>' . $location->domain . '</url>
    <email>' . $location->email . '</email>
    <phone>' . $location->phone . '</phone>
    <address type="work">
     <street>' . $location->address . '</street>
     <city>' . $location->city . '</city>
     <regioncode>' . $location->region . '</regioncode>
     <postalcode>' . $location->postalcode . '</postalcode>
     <country>' . $location->country . '</country>
    </address>
   </contact>
   <provider>
    <name part="full">TrailerCentral</name>
   </provider>
  </vendor>
 </prospect>
</adf>';
    }

    /**
     * Get Non-ADF Formatted XML Data
     * 
     * @param Lead $lead
     * @param User $dealer
     * @param DealerLocation $location
     * @param Inventory || null $inventory
     * @return type
     */
    private function getNoAdfXml(Lead $lead, User $dealer, DealerLocation $location, $inventory = null) {
        return '<?xml version="1.0" encoding="UTF-8"?>
<lead>
 <prospect>
  <requestdate>' . $lead->date_submitted . '</requestdate>
  <vehicle>
   <year>' . ($inventory->year ?? '') . '</year>
   <make>' . ($inventory->make ?? '') . '</make>
   <model>' . ($inventory->model ?? '') . '</model>
   <stock>' . ($inventory->stock ?? '') . '</stock>
   <vin>' . ($inventory->vin ?? '') . '</vin>
  </vehicle>
  <customer>
   <contact>
    <name part="first">' . $lead->first_name . '</name>
    <name part="last">' . $lead->last_name . '</name>
    <email>' . ($lead->email_address ?? '') . '</email>
    <phone>' . ($lead->phone_number ?? '') . '</phone>
   </contact>
   <comments><![CDATA[' . $lead->comments . ']]></comments>
   <address type="home">
    <street>' . $lead->address . '</street>
    <city>' . $lead->city . '</city>
    <regioncode>' . $lead->state . '</regioncode>
    <postalcode>' . $lead->zip . '</postalcode>
   </address>
  </customer>
  <vendor>
   <id sequence="1" source="DealerID">' . $dealer->dealer_id . '</id>
   <id sequence="2" source="DealerLocationID">' . $dealer->dealer_location_id . '</id>
   ' . ($lead->identifier ? '<id sequence="3" source="ID">' . $lead->identifier . '</id>' : '') . '
   <vendorname>' . $dealer->name . '</vendorname>
   <contact>
    <name part="full">' . $location->contact . '</name>
    <url>' . $location->domain . '</url>
    <email>' . $location->email . '</email>
    <phone>' . $location->phone . '</phone>
    <address type="work">
     <street>' . $location->address . '</street>
     <city>' . $location->city . '</city>
     <regioncode>' . $location->region . '</regioncode>
     <postalcode>' . $location->postalcode . '</postalcode>
     <country>' . $location->country . '</country>
    </address>
   </contact>
   <provider>
    <name part="full">TrailerCentral</name>
   </provider>
  </vendor>
 </prospect>
</lead>';
    }

    /**
     * Get Parsed Email
     * 
     * @param string $id
     * @param EmailHistory $email
     * @return ParsedEmail
     */
    private function getParsedEmail($id, $email, $body) {
        // Create Parsed Email
        $parsed = new ParsedEmail();
        $parsed->setId((string) $id);

        // Set To/From
        $parsed->setToEmail(config('adf.imports.gmail.email'));
        $parsed->setFromEmail($email);

        // Set Subject/Body
        $parsed->setSubject('ADF Import');
        $parsed->setBody($body);

        // Return ParsedEmail
        return $parsed;
    }


    /**
     * Get System Email
     * 
     * @return SystemEmail
     */
    private function getSystemEmail(): Email {
        // Get Email
        $email = config('adf.imports.gmail.email');

        // Get System Email With Access Token
        $systemEmail = $this->emails->find(['email' => $email]);

        // No System Email?
        if(empty($systemEmail->id)) {
            $systemEmail = $this->emails->create(['email' => $email]);
        }

        // Google Token Doesn't Exist?!
        if(empty($systemEmail->googleToken)) {
            // Create Access Token
            $accessToken = $this->getAccessToken($systemEmail->id);
            $systemEmail->setRelation('googleToken', $accessToken);
        }

        // Return SystemEmail
        return $systemEmail;
    }

    /**
     * Get Access Token
     * 
     * @param int $emailId
     * @return AccessToken
     */
    private function getAccessToken(int $emailId): AccessToken {
        // Get Expires
        $expiresIn = env('TEST_ADF_EXPIRES_IN');
        $expiredAt = Carbon::now()->addSeconds($expiresIn)->toDateTimeString();

        // Return SystemEmail
        return $this->tokens->create([
            'dealer_id' => 0,
            'token_type' => 'google',
            'relation_type' => 'system_emails',
            'relation_id' => $emailId,
            'access_token' => env('TEST_ADF_ACCESS_TOKEN'),
            'refresh_token' => env('TEST_ADF_REFRESH_TOKEN'),
            'id_token' => env('TEST_ADF_ID_TOKEN'),
            'expires_in' => $expiresIn,
            'expired_at' => $expiredAt,
            'issued_at' => env('TEST_ADF_ISSUED_AT'),
            'scopes' => explode(" ", env('TEST_ADF_SCOPES'))
        ]);
    }

    /**
     * Refresh Lead Imports
     * 
     * @param User $dealer
     * @param DealerLocation $location
     * @return array of LeadImport
     */
    private function refreshLeadImports(User $dealer, DealerLocation $location) {
        // Delete Existing Lead Imports
        $this->imports->delete(['dealer_id' => $dealer->dealer_id]);

        // Create Lead Import for No Location
        $imports = [];
        $imports[] = factory(LeadImport::class, 1)->create([
            'dealer_id' => $dealer->dealer_id,
            'dealer_location_id' => 0,
            'email' => $dealer->email
        ]);

        // Create Lead Import for Location
        $imports[] = factory(LeadImport::class, 1)->create([
            'dealer_id' => $dealer->dealer_id,
            'dealer_location_id' => $location->dealer_location_id,
            'email' => $location->email
        ]);

        // Return Imports
        return $imports;
    }
}