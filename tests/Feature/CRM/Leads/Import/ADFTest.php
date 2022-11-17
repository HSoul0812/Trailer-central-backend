<?php

declare(strict_types=1);

namespace Tests\Feature\CRM\Leads\Import;

use App\Models\CRM\Leads\Lead;
use App\Models\Integration\Auth\AccessToken;
use App\Models\Inventory\Inventory;
use App\Models\User\User;
use App\Models\User\DealerLocation;
use App\Models\System\Email;
use App\Services\Integration\Common\DTOs\ParsedEmail;
use App\Services\Integration\Common\DTOs\ValidateToken;
use App\Services\Integration\Google\GoogleServiceInterface;
use App\Services\Integration\Google\GmailServiceInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Mockery;
use Tests\TestCase;
use Tests\database\seeds\CRM\Leads\ADFSeeder;

class ADFTest extends TestCase
{
    /**
     * App\Repositories\CRM\Leads\LeadRepositoryInterface $leads
     * App\Repositories\System\EmailRepositoryInterface $emails
     * App\Repositories\Integration\Auth\TokenRepositoryInterface $tokens
     */
    protected $leads;
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
        $this->emails = $this->app->make('App\Repositories\System\EmailRepositoryInterface');
        $this->tokens = $this->app->make('App\Repositories\Integration\Auth\TokenRepositoryInterface');
    }


    /**
     * Test Importing ADF Emails
     *
     * @group CRM
     * @covers App\Console\Commands\CRM\Leads\Import\ADF
     * @return void
     */
    public function testADFImport(): void
    {
        $seeder = new ADFSeeder;
        $seeder->seed();
        // Get Dealer
        $dealer = $seeder->dealer;
        $websiteId = $seeder->website->getKey();

        // Create Dealer Location
        $location = $seeder->location;

        // Get System Email
        $systemEmail = $this->getSystemEmail();

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
        $leadsVehicleLocation = [];
        $leadsVehicleNoLocation = [];
        foreach($vehicles as $vehicle) {
            // Create Leads for Vehicle With Location
            $leadsVehicleLocation[] = factory(Lead::class, 1)->make([
                'website_id' => $websiteId,
                'dealer_id' => $dealer->dealer_id,
                'dealer_location_id' => $location->dealer_location_id,
                'inventory_id' => $vehicle->inventory_id
            ])->first();

            // Create Leads for Vehicle With No Location
            $leadsVehicleNoLocation[] = factory(Lead::class, 1)->make([
                'website_id' => $websiteId,
                'dealer_id' => $dealer->dealer_id,
                'dealer_location_id' => 0,
                'inventory_id' => $vehicle->inventory_id
            ])->first();
        }

        // Create Leads With No Inventory
        $leadsNoVehicle = factory(Lead::class, 2)->make([
            'website_id' => $websiteId,
            'dealer_id' => $dealer->dealer_id,
            'dealer_location_id' => 0,
            'inventory_id' => 0
        ]);

        // Create Valid Leads To Not Be Imported By Email
        $leadsValidNoImport = factory(Lead::class, 2)->make([
            'website_id' => $websiteId,
            'dealer_id' => $dealer->dealer_id,
            'dealer_location_id' => 0,
            'inventory_id' => 0
        ]);

        // Create Leads To Not Be Imported By Invalid ADF
        $leadsInvalidAdf = factory(Lead::class, 2)->make([
            'website_id' => $websiteId,
            'dealer_id' => $dealer->dealer_id,
            'dealer_location_id' => 0,
            'inventory_id' => 0
        ]);

        // Create Leads To Not Be Imported By Invalid XML
        $leadsInvalidXml = factory(Lead::class, 2)->make([
            'website_id' => $websiteId,
            'dealer_id' => $dealer->dealer_id,
            'dealer_location_id' => 0,
            'inventory_id' => 0
        ]);

        // Get Messages
        $messages = [];
        $parsed = [];
        $id = 0;
        $toAddress = $dealer->getKey() . '@' . config('adf.imports.gmail.domain');
        foreach($leadsVehicleLocation as $lead) {
            $body = $this->getAdfXml($lead, $dealer, $location, $vehicles[$lead->inventory_id]);
            $parsed[] = $this->getParsedEmail($id, $location->email, $body, $toAddress);
            $messages[] = $id;
            $id++;
        }
        foreach($leadsVehicleNoLocation as $lead) {
            $body = $this->getAdfXml($lead, $dealer, null, $vehicles[$lead->inventory_id]);
            $parsed[] = $this->getParsedEmail($id, $dealer->email, $body, $toAddress);
            $messages[] = $id;
            $id++;
        }
        foreach($leadsNoVehicle as $lead) {
            $body = $this->getAdfXml($lead, $dealer);
            $parsed[] = $this->getParsedEmail($id, $dealer->email, $body, $toAddress);
            $messages[] = $id;
            $id++;
        }
        foreach($leadsValidNoImport as $lead) {
            $body = $this->getAdfXml($lead, $dealer);
            $parsed[] = $this->getParsedEmail($id, $dealer->email, $body, $lead->email_address);
            $messages[] = $id;
            $id++;
        }
        foreach($leadsInvalidAdf as $lead) {
            $body = $this->getNoAdfXml($lead, $dealer);
            $parsed[] = $this->getParsedEmail($id, $dealer->email, $body, $toAddress);
            $messages[] = $id;
            $id++;
        }
        foreach($leadsInvalidXml as $lead) {
            $parsed[] = $this->getParsedEmail($id, $dealer->email, $lead->comments, $toAddress);
            $messages[] = $id;
            $id++;
        }

        // Mock Gmail Service
        $this->mock(GoogleServiceInterface::class, function ($mock) use($systemEmail) {
            
            $mock->shouldReceive('setKey')
                ->once();

            // Should Receive Messages With Args Once Per Folder!
            $mock->shouldReceive('validate')
                 ->with(Mockery::on(function($accessToken) use($systemEmail) {
                    if($systemEmail->id == $accessToken->relation_id) {
                        return true;
                    }
                    return false;
                 }))
                 ->once()
                 ->andReturn(new ValidateToken([
                    'is_valid' => true,
                    'is_expired' => false,
                    'new_token' => [],
                 ]));
        });

        // Mock Gmail Service
        $this->mock(GmailServiceInterface::class, function ($mock) use($messages, $parsed) {
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
            }

            // Should Receive Move For Every Message
            $mock->shouldReceive('move')
                 ->times(count($messages))
                 ->andReturn(true);
        });

        // Call Import ADF Leads
        $this->withoutMockingConsoleOutput()->artisan('leads:import');
        $output = Artisan::output();
        $this->assertStringContainsString("Imported 6 leads from import service", $output);

        // Assert Leads Exist
        foreach($leadsVehicleLocation as $lead) {
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
        foreach($leadsVehicleNoLocation as $lead) {
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
        foreach($leadsNoVehicle as $lead) {
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
        foreach($leadsValidNoImport as $lead) {
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
        foreach($leadsInvalidAdf as $lead) {
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
        foreach($leadsInvalidXml as $lead) {
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

        $seeder->cleanUp();
    }


    /**
     * Get ADF Formatted XML Data
     *
     * @param Lead $lead
     * @param User $dealer
     * @param DealerLocation || null $location
     * @param Inventory || null $inventory
     * @return string
     */
    private function getAdfXml(Lead $lead, User $dealer, $location = null, $inventory = null): string {
        return '<?xml version="1.0" encoding="UTF-8"?>
<?adf version="1.0"?>
<adf>
 <prospect>
  <requestdate>' . $lead->date_submitted . '</requestdate>
  <vehicle>
   <year>' . ($inventory->year ?? '') . '</year>
   <make>' . (!empty($inventory->manufacturer) ? urlencode($inventory->manufacturer) : '') . '</make>
   <model>' . (!empty($inventory->model) ? urlencode($inventory->model) : '') . '</model>
   <stock>' . (!empty($inventory->stock) ? urlencode($inventory->stock) : '') . '</stock>
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
   ' . (!empty($location->dealer_location_id) ? '<id sequence="2" source="DealerLocationID">' . $location->dealer_location_id . '</id>' : '') . '
   ' . ($lead->identifier ? '<id sequence="3" source="ID">' . $lead->identifier . '</id>' : '') . '
   <vendorname>' . $dealer->name . '</vendorname>
   <contact>
    <name part="full">' . ($location->contact ?? $dealer->name) . '</name>
    <url>' . ($location->domain ?? $dealer->website->domain) . '</url>
    <email>' . ($location->email ?? $dealer->email) . '</email>
    <phone>' . ($location->phone ?? '') . '</phone>
    <address type="work">
     <street>' . ($location->address ?? '') . '</street>
     <city>' . ($location->city ?? '') . '</city>
     <regioncode>' . ($location->region ?? '') . '</regioncode>
     <postalcode>' . ($location->postalcode ?? '') . '</postalcode>
     <country>' . ($location->country ?? '') . '</country>
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
     * @param DealerLocation || null $location
     * @param Inventory || null $inventory
     * @return string
     */
    private function getNoAdfXml(Lead $lead, User $dealer, $location = null, $inventory = null): string {
        return '<?xml version="1.0" encoding="UTF-8"?>
<lead>
 <prospect>
  <requestdate>' . $lead->date_submitted . '</requestdate>
  <vehicle>
   <year>' . ($inventory->year ?? '') . '</year>
   <make>' . (!empty($inventory->manufacturer) ? urlencode($inventory->manufacturer) : '') . '</make>
   <model>' . (!empty($inventory->model) ? urlencode($inventory->model) : '') . '</model>
   <stock>' . (!empty($inventory->stock) ? urlencode($inventory->stock) : '') . '</stock>
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
   ' . (!empty($location->dealer_location_id) ? '<id sequence="2" source="DealerLocationID">' . $location->dealer_location_id . '</id>' : '') . '
   ' . ($lead->identifier ? '<id sequence="3" source="ID">' . $lead->identifier . '</id>' : '') . '
   <vendorname>' . $dealer->name . '</vendorname>
   <contact>
    <name part="full">' . ($location->contact ?? $dealer->name) . '</name>
    <url>' . ($location->domain ?? $dealer->website->domain) . '</url>
    <email>' . ($location->email ?? $dealer->email) . '</email>
    <phone>' . ($location->phone ?? '') . '</phone>
    <address type="work">
     <street>' . ($location->address ?? '') . '</street>
     <city>' . ($location->city ?? '') . '</city>
     <regioncode>' . ($location->region ?? '') . '</regioncode>
     <postalcode>' . ($location->postalcode ?? '') . '</postalcode>
     <country>' . ($location->country ?? '') . '</country>
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
     * @param int $id
     * @param string $from
     * @param string $body
     * @param string || null $to
     * @return ParsedEmail
     */
    private function getParsedEmail(int $id, string $from, string $body, $to = null): ParsedEmail {
        // Create Parsed Email
        $parsed = new ParsedEmail();
        $parsed->setId((string) $id);

        // To Is Null?
        if($to === null) {
            $to = self::getTestDealerId() . '@' . config('adf.imports.gmail.domain');
        }

        // Set To/From
        $parsed->setToEmail($to);
        $parsed->setFromEmail($from);

        // Set Subject/Body
        $parsed->setSubject('ADF Import');
        $parsed->setBody($body);

        // Return ParsedEmail
        return $parsed;
    }


    /**
     * Get System Email
     *
     * @return Email
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
}
