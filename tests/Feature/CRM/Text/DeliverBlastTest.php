<?php

namespace Tests\Feature\CRM\Text;

use App\Models\CRM\Text\BlastSent;
use App\Services\CRM\Text\TwilioServiceInterface;
use App\Models\CRM\Leads\Lead;
use Faker\Factory as Faker;
use Mockery;
use Mockery\LegacyMockInterface;
use Tests\database\seeds\CRM\Text\BlastSeeder;
use Tests\TestCase;

class DeliverBlastTest extends TestCase
{
    /**
     * App\Repositories\CRM\Text\BlastRepositoryInterface $blasts
     * App\Repositories\CRM\Text\TemplateRepositoryInterface $templates
     * App\Repositories\User\DealerLocationRepository $dealerLocation
     */
    protected $blasts;
    protected $templates;
    protected $dealerLocation;

    /**
     * @var TwilioServiceInterface|LegacyMockInterface
     */
    protected $twilioServiceMock;

    /**
     * Faker\Generator $faker
     */
    protected $faker;

    /**
     * @const int
     */
    const ENTITY_TYPE_ID = 1;

    /**
     * Set Up Test
     */
    public function setUp(): void
    {
        parent::setUp();

        // Make Lead Repo
        $this->blasts = $this->app->make('App\Repositories\CRM\Text\BlastRepositoryInterface');
        $this->templates = $this->app->make('App\Repositories\CRM\Text\TemplateRepositoryInterface');
        $this->dealerLocation = $this->app->make('App\Repositories\User\DealerLocationRepositoryInterface');

        $this->instanceMock('twilioServiceMock', TwilioServiceInterface::class);

        $this->faker = Faker::create();
    }

    /**
     * @group CRM
     * @specs string action = inquired
     * @specs array location_id = first
     * @specs int send_after_days = 45
     * @specs int include_archived = 0
     * @return void
     */
    public function testSimpleBlast()
    {
        $seeder = new BlastSeeder('inquired');

        $seeder->seed();

        $inquiredBlasts = $seeder->inquiredBlasts;
        $deliveredBlasts = $seeder->deliveredBlasts;
        $otherLeads = $seeder->otherLeads;

        foreach ($inquiredBlasts as $blast) {
            $this->twilioServiceMock
                ->shouldReceive('isValidPhoneNumber')
                ->once()
                ->with($blast->from_sms_number)
                ->andReturn(true);
        }

        foreach ($otherLeads as $lead) {
            foreach ($inquiredBlasts as $blast) {
                $this->twilioServiceMock
                    ->shouldReceive('send')
                    ->once()
                    ->with(
                        $blast->from_sms_number,
                        $lead->text_phone,
                        Mockery::on(function ($text) {
                            return is_string($text);
                        }),
                        $lead->full_name
                    );
            }
        }

        $this->artisan('text:deliver-blast ' . $seeder->dealer->dealer_id)->assertExitCode(0);

        foreach ($otherLeads as $lead) {
            $this->assertDatabaseHas('crm_tc_lead_status', [
                'tc_lead_identifier' => $lead->identifier
            ]);

            foreach ($inquiredBlasts as $blast) {
                $this->assertDatabaseHas('dealer_texts_log', [
                    'lead_id'     => $lead->identifier,
                    'from_number' => $blast->from_sms_number,
                    'to_number'   => $lead->text_phone,
                ]);

                $this->assertDatabaseHas('crm_text_blast_sent', [
                    'text_blast_id' => $blast->id,
                    'lead_id' => $lead->identifier,
                    'status' => BlastSent::STATUS_LOGGED
                ]);
            }

            foreach ($deliveredBlasts as $blast) {
                $this->assertDatabaseMissing('dealer_texts_log', [
                    'lead_id'     => $lead->identifier,
                    'from_number' => $blast->from_sms_number,
                    'to_number'   => $lead->text_phone,
                ]);

                $this->assertDatabaseMissing('crm_text_blast_sent', [
                    'text_blast_id' => $blast->id,
                    'lead_id' => $lead->identifier,
                    'status' => BlastSent::STATUS_LOGGED
                ]);
            }
        }

        foreach ($inquiredBlasts as $blast) {
            $this->assertDatabaseHas('crm_text_blast', [
                'id' => $blast->id,
                'is_delivered' => 1,
                'is_error' => false,
            ]);
        }

        $seeder->cleanUp();
    }

    /**
     * Test blast on purchases
     *
     * @group CRM
     * @specs string action = purchased
     * @specs array location_id = null
     * @specs int send_after_days = 15
     * @specs int include_archived = 0
     * @return void
     */
    public function testPurchasedBlast()
    {
        $seeder = new BlastSeeder('purchased');

        $seeder->seed();

        $purchasedBlast = $seeder->purchasedBlasts;
        $deliveredBlasts = $seeder->deliveredBlasts;
        $wonLeads = $seeder->wonLeads;

        foreach ($purchasedBlast as $blast) {
            $this->twilioServiceMock
                ->shouldReceive('isValidPhoneNumber')
                ->once()
                ->with($blast->from_sms_number)
                ->andReturn(true);
        }

        foreach ($wonLeads as $lead) {
            foreach ($purchasedBlast as $blast) {
                $this->twilioServiceMock
                    ->shouldReceive('send')
                    ->once()
                    ->with(
                        $blast->from_sms_number,
                        $lead->text_phone,
                        Mockery::on(function ($text) {
                            return is_string($text);
                        }),
                        $lead->full_name
                    );
            }
        }

        $this->artisan('text:deliver-blast ' . $seeder->dealer->dealer_id)->assertExitCode(0);

        foreach ($wonLeads as $lead) {
            $this->assertDatabaseHas('crm_tc_lead_status', [
                'tc_lead_identifier' => $lead->identifier
            ]);

            foreach ($purchasedBlast as $blast) {
                $this->assertDatabaseHas('dealer_texts_log', [
                    'lead_id'     => $lead->identifier,
                    'from_number' => $blast->from_sms_number,
                    'to_number'   => $lead->text_phone,
                ]);

                $this->assertDatabaseHas('crm_text_blast_sent', [
                    'text_blast_id' => $blast->id,
                    'lead_id' => $lead->identifier,
                    'status' => BlastSent::STATUS_LOGGED
                ]);
            }

            foreach ($deliveredBlasts as $blast) {
                $this->assertDatabaseMissing('dealer_texts_log', [
                    'lead_id'     => $lead->identifier,
                    'from_number' => $blast->from_sms_number,
                    'to_number'   => $lead->text_phone,
                ]);

                $this->assertDatabaseMissing('crm_text_blast_sent', [
                    'text_blast_id' => $blast->id,
                    'lead_id' => $lead->identifier,
                    'status' => BlastSent::STATUS_LOGGED
                ]);
            }
        }

        foreach ($purchasedBlast as $blast) {
            $this->assertDatabaseHas('crm_text_blast', [
                'id' => $blast->id,
                'is_delivered' => 1,
                'is_error' => false,
            ]);
        }

        $seeder->cleanUp();
    }

    /**
     * Test blast by archived status
     *
     * @group CRM
     * @specs string action = inquired
     * @specs array location_id = any
     * @specs int send_after_days = 15
     * @specs int include_archived = 1
     * @return void
     */
    public function testArchivedBlast()
    {
        $seeder = new BlastSeeder('inquired', '1');

        $seeder->seed();

        $inquiredBlasts = $seeder->inquiredBlasts;
        $otherLeads = $seeder->otherLeads;

        foreach ($inquiredBlasts as $blast) {
            $this->twilioServiceMock
                ->shouldReceive('isValidPhoneNumber')
                ->once()
                ->with($blast->from_sms_number)
                ->andReturn(true);
        }

        foreach ($otherLeads as $lead) {
            foreach ($inquiredBlasts as $blast) {
                if (!$lead->is_archived) {
                    continue;
                }

                $this->twilioServiceMock
                    ->shouldReceive('send')
                    ->once()
                    ->with(
                        $blast->from_sms_number,
                        $lead->text_phone,
                        Mockery::on(function ($text) {
                            return is_string($text);
                        }),
                        $lead->full_name
                    );
            }
        }

        $this->artisan('text:deliver-blast ' . $seeder->dealer->dealer_id)->assertExitCode(0);

        foreach ($otherLeads as $lead) {
            if ($lead->is_archived) {
                $this->assertDatabaseHas('crm_tc_lead_status', [
                    'tc_lead_identifier' => $lead->identifier
                ]);
            }

            foreach ($inquiredBlasts as $blast) {
                if ($lead->is_archived) {
                    $this->assertDatabaseHas('dealer_texts_log', [
                        'lead_id' => $lead->identifier,
                        'from_number' => $blast->from_sms_number,
                        'to_number' => $lead->text_phone,
                    ]);

                    $this->assertDatabaseHas('crm_text_blast_sent', [
                        'text_blast_id' => $blast->id,
                        'lead_id' => $lead->identifier,
                        'status' => BlastSent::STATUS_LOGGED
                    ]);
                } else {
                    $this->assertDatabaseMissing('dealer_texts_log', [
                        'lead_id' => $lead->identifier,
                        'from_number' => $blast->from_sms_number,
                        'to_number' => $lead->text_phone,
                    ]);

                    $this->assertDatabaseMissing('crm_text_blast_sent', [
                        'text_blast_id' => $blast->id,
                        'lead_id' => $lead->identifier,
                        'status' => BlastSent::STATUS_LOGGED
                    ]);
                }
            }
        }

        foreach ($inquiredBlasts as $blast) {
            $this->assertDatabaseHas('crm_text_blast', [
                'id' => $blast->id,
                'is_delivered' => 1,
                'is_error' => false,
            ]);
        }

        $seeder->cleanUp();
    }

    /**
     * Test blast with brands
     *
     * @group CRM
     * @specs string action = inquired
     * @specs array location_id = any
     * @specs int send_after_days = 15
     * @specs int include_archived = 0
     * @return void
     */
    public function testBrandBlast()
    {
        $seeder = new BlastSeeder('inquired', 0, false, true);

        $seeder->seed();

        $inquiredBlasts = $seeder->inquiredBlasts;
        $otherLeads = $seeder->otherLeads;

        $blastsBrands = [];

        foreach ($inquiredBlasts as $inquiredBlast) {
            foreach ($inquiredBlast->brands as $brand) {
                $blastsBrands[] = $brand->brand;
            }
        }

        foreach ($inquiredBlasts as $blast) {
            $this->twilioServiceMock
                ->shouldReceive('isValidPhoneNumber')
                ->once()
                ->with($blast->from_sms_number)
                ->andReturn(true);
        }

        foreach ($otherLeads as $lead) {
            foreach ($inquiredBlasts as $blast) {
                if (!$lead->inventory || !in_array($lead->inventory->brand, $blastsBrands)) {
                    continue;
                }

                $this->twilioServiceMock
                    ->shouldReceive('send')
                    ->once()
                    ->with(
                        $blast->from_sms_number,
                        $lead->text_phone,
                        Mockery::on(function ($text) {
                            return is_string($text);
                        }),
                        $lead->full_name
                    );
            }
        }

        $this->artisan('text:deliver-blast ' . $seeder->dealer->dealer_id)->assertExitCode(0);

        foreach ($otherLeads as $lead) {
            if ($lead->inventory && in_array($lead->inventory->brand, $blastsBrands)) {
                $this->assertDatabaseHas('crm_tc_lead_status', [
                    'tc_lead_identifier' => $lead->identifier
                ]);

                foreach ($inquiredBlasts as $blast) {
                    $this->assertDatabaseHas('dealer_texts_log', [
                        'lead_id'     => $lead->identifier,
                        'from_number' => $blast->from_sms_number,
                        'to_number'   => $lead->text_phone,
                    ]);

                    $this->assertDatabaseHas('crm_text_blast_sent', [
                        'text_blast_id' => $blast->id,
                        'lead_id' => $lead->identifier,
                        'status' => BlastSent::STATUS_LOGGED
                    ]);
                }
            } else {
                foreach ($inquiredBlasts as $blast) {
                    $this->assertDatabaseMissing('dealer_texts_log', [
                        'lead_id'     => $lead->identifier,
                        'from_number' => $blast->from_sms_number,
                        'to_number'   => $lead->text_phone,
                    ]);

                    $this->assertDatabaseMissing('crm_text_blast_sent', [
                        'text_blast_id' => $blast->id,
                        'lead_id' => $lead->identifier,
                        'status' => BlastSent::STATUS_LOGGED
                    ]);
                }
            }
        }

        foreach ($inquiredBlasts as $blast) {
            $this->assertDatabaseHas('crm_text_blast', [
                'id' => $blast->id,
                'is_delivered' => 1,
                'is_error' => false,
            ]);
        }

        $seeder->cleanUp();
    }

    /**
     * Test blast with categories
     *
     * @group CRM
     * @specs string action = inquired
     * @specs array location_id = any
     * @specs int send_after_days = 15
     * @specs int include_archived = 0
     * @return void
     */
    public function testCategoryBlast()
    {
        $seeder = new BlastSeeder('inquired', 0, true);

        $seeder->seed();

        $inquiredBlasts = $seeder->inquiredBlasts;
        $otherLeads = $seeder->otherLeads;

        $blastsCategories = [];

        foreach ($inquiredBlasts as $inquiredBlast) {
            foreach ($inquiredBlast->categories as $category) {
                $blastsCategories[] = $category->category;
            }
        }

        foreach ($inquiredBlasts as $blast) {
            $this->twilioServiceMock
                ->shouldReceive('isValidPhoneNumber')
                ->once()
                ->with($blast->from_sms_number)
                ->andReturn(true);
        }

        foreach ($otherLeads as $lead) {
            foreach ($inquiredBlasts as $blast) {
                if (!$lead->inventory || !in_array($lead->inventory->category, $blastsCategories)) {
                    continue;
                }

                $this->twilioServiceMock
                    ->shouldReceive('send')
                    ->once()
                    ->with(
                        $blast->from_sms_number,
                        $lead->text_phone,
                        Mockery::on(function ($text) {
                            return is_string($text);
                        }),
                        $lead->full_name
                    );
            }
        }

        $this->artisan('text:deliver-blast ' . $seeder->dealer->dealer_id)->assertExitCode(0);

        foreach ($otherLeads as $lead) {
            if ($lead->inventory && in_array($lead->inventory->category, $blastsCategories)) {
                $this->assertDatabaseHas('crm_tc_lead_status', [
                    'tc_lead_identifier' => $lead->identifier
                ]);

                foreach ($inquiredBlasts as $blast) {
                    $this->assertDatabaseHas('dealer_texts_log', [
                        'lead_id'     => $lead->identifier,
                        'from_number' => $blast->from_sms_number,
                        'to_number'   => $lead->text_phone,
                    ]);

                    $this->assertDatabaseHas('crm_text_blast_sent', [
                        'text_blast_id' => $blast->id,
                        'lead_id' => $lead->identifier,
                        'status' => BlastSent::STATUS_LOGGED
                    ]);
                }
            } else {
                foreach ($inquiredBlasts as $blast) {
                    $this->assertDatabaseMissing('dealer_texts_log', [
                        'lead_id'     => $lead->identifier,
                        'from_number' => $blast->from_sms_number,
                        'to_number'   => $lead->text_phone,
                    ]);

                    $this->assertDatabaseMissing('crm_text_blast_sent', [
                        'text_blast_id' => $blast->id,
                        'lead_id' => $lead->identifier,
                        'status' => BlastSent::STATUS_LOGGED
                    ]);
                }
            }
        }

        foreach ($inquiredBlasts as $blast) {
            $this->assertDatabaseHas('crm_text_blast', [
                'id' => $blast->id,
                'is_delivered' => 1,
                'is_error' => false,
            ]);
        }

        $seeder->cleanUp();
    }
}
