<?php

namespace Tests\Integration\Http\Controllers\User;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User\AuthToken;
use App\Models\User\User;
use App\Models\CRM\User\Settings;
use App\Models\User\CrmUser;
use App\Models\User\NewUser;
use App\Models\User\NewDealerUser;
use App\Repositories\User\NewDealerUserRepositoryInterface;
use App\Repositories\CRM\User\CrmUserRepositoryInterface;
use App\Services\CRM\User\SettingsServiceInterface;

class SettingsControllerTest extends TestCase {

    use WithFaker;

    /** @var User */
    protected $dealer;

    /** @var AuthToken */
    protected $token;

    const apiEndpoint = '/api/user/crm/settings';

    public function setUp(): void
    {
        parent::setUp();

        $this->dealer = factory(User::class)->create([
            'type' => User::TYPE_DEALER,
            'state' => User::STATUS_ACTIVE
        ]);

        $this->token = $this->dealer->authToken->access_token;

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
    }

    public function tearDown(): void
    {
        $userId = $this->dealer->newDealerUser->user_id;

        Settings::where('user_id', $userId)->delete();

        // Delete CRM User Related Data
        NewDealerUser::where(['user_id' => $userId])->delete();
        CrmUser::where(['user_id' => $userId])->delete();
        NewUser::destroy($userId);

        $this->dealer->authToken->delete();

        $this->dealer->delete();

        parent::tearDown();
    }

    /**
     * @return array[]
     */
    public function settingsParamDataProvider()
    {
        return [
            [
                [
                    'enable_hot_potato' => 0,
                    'disable_daily_digest' => 1,
                    'enable_assign_notification' => 0,
                    'default/filters/sort' => 1,
                    'round-robin/hot-potato/skip-weekends' => 0,
                    'round-robin/hot-potato/use-submission-date' => 1
                ]
            ]
        ];
    }

    /**
     * @group CRM
     * @dataProvider settingsParamDataProvider
     * @param array of dataProvider
     */
    public function testUpdate($settingsParams)
    {
        $userId = $this->dealer->newDealerUser->user_id;

        $response = $this->withHeaders(['access-token' => $this->token])
            ->postJson(self::apiEndpoint, $settingsParams)
            ->assertSuccessful();

        $response->assertJsonStructure([
            'data' => [
                'enable_hot_potato',
                'disable_daily_digest',
                'enable_assign_notification',
                'default/filters/sort',
                'round-robin/hot-potato/skip-weekends',
                'round-robin/hot-potato/use-submission-date'
            ]
        ]);

        $this->assertDatabaseHas('new_crm_user', [
            'user_id' => $userId,
            'enable_hot_potato' => 0,
            'disable_daily_digest' => 1,
            'enable_assign_notification' => 0
        ]);

        $this->assertDatabaseHas('crm_settings', [
            'user_id' => $userId,
            'key' => 'default/filters/sort',
            'value' => 1
        ]);

        $this->assertDatabaseHas('crm_settings', [
            'user_id' => $userId,
            'key' => 'round-robin/hot-potato/skip-weekends',
            'value' => 0
        ]);

        $this->assertDatabaseHas('crm_settings', [
            'user_id' => $userId,
            'key' => 'round-robin/hot-potato/use-submission-date',
            'value' => 1
        ]);
    }

    /**
     * @group CRM
     * @dataProvider settingsParamDataProvider
     * @param array of dataProvider
     */
    public function testGetAll($settingsParams)
    {
        $userId = $this->dealer->newDealerUser->user_id;
        $settingsParams['user_id'] = $userId;

        $service = app(SettingsServiceInterface::class);
        $service->update($settingsParams);

        $response = $this->withHeaders(['access-token' => $this->token])
            ->getJson(self::apiEndpoint)
            ->assertSuccessful();

        $response->assertJsonStructure([
            'data' => [
                'user_id',
                'enable_hot_potato',
                'disable_daily_digest',
                'enable_assign_notification',
                'default/filters/sort',
                'round-robin/hot-potato/skip-weekends',
                'round-robin/hot-potato/use-submission-date'
            ]
        ]);
    }
}