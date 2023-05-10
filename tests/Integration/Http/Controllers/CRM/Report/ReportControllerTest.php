<?php

namespace Tests\Integration\Http\Controllers\CRM\Report;

use Tests\Integration\IntegrationTestCase;
use App\Models\CRM\Report\Report;
use Mockery;
use Mockery\LegacyMockInterface;
use App\Models\User\NewUser;
use App\Models\User\CrmUser;
use App\Models\User\NewDealerUser;
use App\Models\User\User;
use App\Models\User\AuthToken;
use App\Repositories\User\NewDealerUserRepositoryInterface;
use App\Repositories\CRM\User\CrmUserRepositoryInterface;
use Faker\Factory;
use Carbon\Carbon;
use App\Models\CRM\Leads\Lead;
use App\Models\Website\Website;
use App\Models\Inventory\Inventory;
use App\Models\User\DealerLocation;
use App\Models\Inventory\AttributeValue;
use App\Models\Inventory\Attribute;
use App\Repositories\CRM\Report\ReportRepositoryInterface;

/**
 * Class ReportControllerTest
 * @package Tests\Integration\Http\Controllers\CRM\Report
 *
 * @coversDefaultClass \App\Http\Controllers\v1\CRM\ReportController
 */
class ReportControllerTest extends IntegrationTestCase
{
    const API_URL = '/api/dms/reports/crm';

    public function setUp(): void
    {
        parent::setUp();

        $this->faker = Factory::create();

        $this->dealer = factory(User::class)->create([
            'type' => User::TYPE_DEALER,
            'state' => User::STATUS_ACTIVE
        ]);

        $this->accessToken = $this->dealer->authToken->access_token;

        /**
         * necessary data for CRM user
         */
        $user = factory(NewUser::class)->create();
        $newDealerUserRepo = app(NewDealerUserRepositoryInterface::class);
        $newDealerUser = $newDealerUserRepo->create([
            'user_id' => $user->user_id,
            'salt' => md5((string)$user->user_id), // random string
            'auto_import_hide' => 0,
            'auto_msrp' => 0
        ]);
        $this->dealer->newDealerUser()->save($newDealerUser);
        $crmUserRepo = app(CrmUserRepositoryInterface::class);
        $crmUserRepo->create([
            'user_id' => $user->user_id,
            'logo' => '',
            'first_name' => '',
            'last_name' => '',
            'display_name' => '',
            'dealer_name' => $this->dealer->name,
            'active' => 1
        ]);
        // END

        // seed Reports
        $this->reports = factory(Report::class, 10)->create([
            'user_id' => $user->user_id
        ]);

        $location = factory(DealerLocation::class)->create([
            'dealer_id' => $this->dealer->getKey()
        ]);

        // seed Inventories
        $inventory = factory(Inventory::class)->create([
            'dealer_id' => $this->dealer->getKey(),
            'dealer_location_id' => $location->getKey(),
            'created_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);

        // seed Leads
        $website = factory(Website::class)->create([
            'dealer_id' => $this->dealer->getKey()
        ]);
        factory(Lead::class, 10)->create([
            'website_id' => $website->getKey(),
            'dealer_id' => $this->dealer->getKey(),
            'dealer_location_id' => $location->getKey(),
            'inventory_id' => $inventory->getKey(),
            'date_submitted' => Carbon::now()->format('Y-m-d H:i:s')
        ]);

        // seed AttributeValue
        $attributeValues = factory(AttributeValue::class, 50)->make([
            'inventory_id' => $inventory->getKey()
        ])->toArray();
        AttributeValue::insertOrIgnore($attributeValues);
        
        // seed AttributeValue pull_type
        $attribute = Attribute::where('code', ReportRepositoryInterface::INVENTORY_ATTRIBUTE_PULL_TYPE_CODE)->first();
        factory(AttributeValue::class)->create([
            'inventory_id' => $inventory->getKey(),
            'attribute_id' => $attribute->getKey()
        ]);
    }

    public function tearDown(): void
    {
        $userId = $this->dealer->newDealerUser->user_id;

        Report::where('user_id', $userId)->delete();

        Lead::where('dealer_id', $this->dealer->getKey())->delete();
        Website::where('dealer_id', $this->dealer->getKey())->delete();

        $inventory = Inventory::where('dealer_id', $this->dealer->getKey())->first();
        AttributeValue::where('inventory_id', $inventory->getKey())->delete();
        $inventory->deleteQuietly();


        DealerLocation::where('dealer_id', $this->dealer->getKey())->delete();

        // Delete CRM User Related Data
        NewDealerUser::where(['user_id' => $userId])->delete();
        CrmUser::where(['user_id' => $userId])->delete();
        NewUser::destroy($userId);
        
        $this->dealer->authToken->delete();

        $this->dealer->delete();

        parent::tearDown();
    }

    /**
     * @group CRM
     */
    public function testIndex()
    {
        $reportType = $this->faker->randomElement(Report::REPORT_TYPES);
        $missingReportTypes = array_values(array_diff(Report::REPORT_TYPES, [$reportType]));
        $userId = $this->dealer->newDealerUser->user_id;
        $totalReports = Report::where('report_type', $reportType)->where('user_id', $userId)->count();

        $response = $this->json(
            'GET', self::API_URL, 
            [
                'report_type' => $reportType
            ], 
            ['access-token' => $this->accessToken]
        );

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'report_id',
                        'report_name',
                        'filters',
                        'user_id',
                        'report_type'
                    ]
                ]
            ])
            ->assertJsonFragment(['report_type' => $reportType])
            ->assertJsonMissing(['report_type' => $missingReportTypes[0]])
            ->assertJsonMissing(['report_type' => $missingReportTypes[1]])
            ->assertJsonCount($totalReports, 'data.*');
    }

    /**
     * @group CRM
     */
    public function testCreate()
    {
        $reportName = $this->faker->md5();
        $reportType = $this->faker->randomElement(Report::REPORT_TYPES);
        $chartSpan = $this->faker->randomElement(['daily', 'monthly']);
        $leadSource = $this->faker->company();
        $userId = $this->dealer->newDealerUser->user_id;

        $response = $this->json(
            'PUT', self::API_URL, 
            [
                'report_name' => $reportName,
                'report_type' => $reportType,
                'p_start' => Carbon::now()->format('Y-m-d'),
                'p_end' => Carbon::now()->endOfMonth()->format('Y-m-d'),
                's_start' => Carbon::now()->startOfYear()->format('Y-m-d'),
                's_end' => Carbon::now()->endOfYear()->format('Y-m-d'),
                'chart_span' => $chartSpan,
                'lead_source' => $leadSource

            ], 
            ['access-token' => $this->accessToken]
        );
        
        $totalReports = Report::where('report_type', $reportType)->where('user_id', $userId)->count();

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'report_id',
                        'report_name',
                        'filters',
                        'user_id',
                        'report_type'
                    ]
                ]
            ])
            ->assertJsonFragment(['report_name' => $reportName])
            ->assertJsonCount($totalReports, 'data.*');
    }

    /**
     * @group CRM
     */
    public function testDelete()
    {
        $reportId = $this->reports[0]->getKey();
        $reportType = $this->reports[0]->report_type;
        $userId = $this->dealer->newDealerUser->user_id;

        $response = $this->json(
            'DELETE', self::API_URL .'/'. $reportId, 
            [], 
            ['access-token' => $this->accessToken]
        );

        $totalReports = Report::where('report_type', $reportType)->where('user_id', $userId)->count();

        $response->assertStatus(200)
            ->assertJsonMissing(['report_id' => $reportId])
            ->assertJsonCount($totalReports, 'data.*');
    }

    public function testGetFilteredLeads()
    {
        $response = $this->json(
            'GET', self::API_URL .'/filtered', 
            [
                'date_from' => Carbon::now()->startOfMonth()->format('Y-m-d'),
                'date_to' => Carbon::now()->endOfMonth()->format('Y-m-d'),
            ], 
            ['access-token' => $this->accessToken]
        );

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'status',
                        'identifier',
                        'sales_person_id',
                        'date_submitted',
                        'lead_type',
                        'first_name',
                        'last_name'
                    ]
                ]
            ])
            ->assertJsonCount(10, 'data.*');
    }

    /**
     * @group CRM
     */
    public function testGetFilteredInventories()
    {
        $response = $this->json(
            'GET', self::API_URL .'/filtered-inventory', 
            [
                'date_from' => Carbon::now()->startOfMonth()->format('Y-m-d'),
                'date_to' => Carbon::now()->endOfMonth()->format('Y-m-d'),
            ], 
            ['access-token' => $this->accessToken]
        );

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'created_at',
                        'manufacturer',
                        'category',
                        'condition'
                    ]
                ]
            ])
            ->assertJsonCount(1, 'data.*');
    }

    /**
     * @group CRM
     */
    public function testGetFilteredInventoriesPullType()
    {
        $response = $this->json(
            'GET', self::API_URL .'/filtered-inventory', 
            [
                'date_from' => Carbon::now()->startOfMonth()->format('Y-m-d'),
                'date_to' => Carbon::now()->endOfMonth()->format('Y-m-d'),
                'filter_by_pull_type' => 'true',
                'object_is_inventory' => '1'
            ], 
            ['access-token' => $this->accessToken]
        );

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'cnt',
                        'pull_type'
                    ]
                ]
            ])
            ->assertJsonCount(1, 'data.*');
    }
}