<?php

declare(strict_types=1);

namespace Tests\Integration\Repositories\User;

use App\Models\User\Settings;
use App\Repositories\User\SettingsRepository;
use App\Repositories\User\SettingsRepositoryInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Collection;
use Tests\database\seeds\User\SettingsSeeder;
use Tests\TestCase;

class SettingsRepositoryTest extends TestCase
{
    /**
     * @var SettingsSeeder
     */
    private $seeder;

    /**
     * Test that SUT is properly bound by the application
     *
     * @typeOfTest IntegrationTestCase
     *
     * @group DMS
     * @group DMS_SETTINGS
     *
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     */
    public function testRepositoryInterfaceIsBound(): void
    {
        $concreteRepository = $this->getConcreteRepository();

        self::assertInstanceOf(SettingsRepository::class, $concreteRepository);
    }

    /**
     * Test that SUT is performing all desired operations (get all by dealer id)
     *
     * @typeOfTest IntegrationTestCase
     * @dataProvider validQueryParametersProvider
     *
     * @group DMS
     * @group DMS_SETTINGS
     *
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     *
     * @covers SettingsRepository::getAll
     */
    public function testGetAll(array $params, int $expectedTotal): void
    {
        // Given I have a collection of inventories
        $this->seeder->seed();

        // When I call getAll
        // Then I got a list of settings
        /** @var Collection $settings */
        $settings = $this->getConcreteRepository()->getAll($this->seeder->extractValues($params));

        // And That list should be Collection instance
        self::assertInstanceOf(Collection::class, $settings);

        // And the total of records should be the expected
        self::assertSame($expectedTotal, $settings->count());
    }

    /**
     * Test that SUT is performing all desired operations (sort and filter) excepts pagination
     *
     * @typeOfTest IntegrationTestCase
     * @dataProvider validFindParametersProvider
     *
     * @group DMS
     * @group DMS_SETTINGS
     *
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     *
     * @covers SettingsRepository::getAll
     */
    public function testFind(array $params): void
    {
        // Given I have a collection of leads
        $this->seeder->seed();

        // Parse Values
        $values = $this->seeder->extractValues($params);

        // When I call find
        // Then I got a single lead source
        /** @var Settings $settings */
        $settings = $this->getConcreteRepository()->find($values);

        // Find must be Settings
        self::assertInstanceOf(Settings::class, $settings);

        // Settings dealer id matches param dealer id
        self::assertSame($settings->dealer_id, $values['dealer_id']);

        // Settings name matches param setting
        self::assertSame($settings->setting, $values['setting']);
    }


    /**
     * Test that SUT is inserting correctly
     *
     * @typeOfTest IntegrationTestCase
     *
     * @group DMS
     * @group DMS_SETTINGS
     *
     * @throws BindingResolutionException when there is a problem with resolution of concreted class
     *
     * @covers SettingsRepository::create
     */
    public function testCreate(): void {
        $this->seeder->seed();

        // Given I have a collection of sources
        $settings = $this->seeder->missingSettings;

        // Get Settings
        $setting = $settings[array_rand($settings, 1)];

        // Setting does not exist yet
        self::assertSame(0, Settings::where(['dealer_id' => $setting->dealer_id, 'setting' => $setting->setting])->count());

        // When I call create with valid parameters
        /** @var Settings $leadSettingsToCustomer */
        $settingsForDealer = $this->getConcreteRepository()->create([
            'dealer_id' => $setting->dealer_id,
            'setting' => $setting->setting,
            'setting_value' => $setting->setting_value
        ]);

        // Then I should get a class which is an instance of Settings
        self::assertInstanceOf(Settings::class, $settingsForDealer);

        // Setting did not exist before but does now after create
        self::assertSame(1, Settings::where(['dealer_id' => $setting->dealer_id, 'setting' => $setting->setting])->count());
    }

    /**
     * Test that SUT is inserting correctly
     *
     * @typeOfTest IntegrationTestCase
     *
     * @group DMS
     * @group DMS_SETTINGS
     *
     * @throws BindingResolutionException when there is a problem with resolution of concreted class
     *
     * @covers SettingsRepository::create
     */
    public function testUpdate(): void {
        $this->seeder->seed();

        // Given I have a collection of created sources
        $settings = $this->seeder->createdSettings;

        // Get Settings
        $setting = $settings[array_rand($settings, 1)];

        // Setting already exists
        self::assertSame(1, Settings::where(['dealer_id' => $setting->dealer_id, 'setting' => $setting->setting])->count());

        // When I call update with valid parameters
        /** @var Settings $leadSettings */
        $settingsForDealer = $this->getConcreteRepository()->update([
            'id' => $setting->id,
            'dealer_id' => $setting->dealer_id,
            'setting' => $setting->setting,
            'setting_value' => $setting->setting_value
        ]);

        // Then I should get a class which is an instance of Settings
        self::assertInstanceOf(Settings::class, $settingsForDealer);

        // Setting should still exist after update
        self::assertSame(1, Settings::where(['dealer_id' => $setting->dealer_id, 'setting' => $setting->setting])->count());
    }

    
    /**
     * Test that SUT is inserting correctly
     *
     * @typeOfTest IntegrationTestCase
     *
     * @group DMS
     * @group DMS_SETTINGS
     *
     * @throws BindingResolutionException when there is a problem with resolution of concreted class
     *
     * @covers SettingsRepository::create
     */
    public function testCreateOrUpdate(): void {
        $this->seeder->seed();

        // Given I have a collection of missing sources
        $missing = $this->seeder->missingSettings;
        $created = $this->seeder->createdSettings;

        // Setting is missing
        $first = reset($created);
        $dealerId = $first->dealer_id;
        self::assertSame(count($created), Settings::where(['dealer_id' => $dealerId])->count());

        // Create Missing Settings
        $settings = [];
        foreach($missing as $setting) {
            // Setting is missing
            self::assertSame(0, Settings::where(['dealer_id' => $setting->dealer_id, 'setting' => $setting->setting])->count());

            // Add Setting
            $settings[] = [
                'setting' => $setting->setting,
                'value' => $setting->setting_value
            ];
        }

        // Create Updated Settings
        foreach($created as $setting) {
            // Setting is missing
            self::assertSame(1, Settings::where(['dealer_id' => $setting->dealer_id, 'setting' => $setting->setting])->count());

            // Add Setting
            $settings[] = [
                'setting' => $setting->setting,
                'value' => $setting->setting_value
            ];
        }

        // When I call create with valid parameters
        /** @var Settings $leadSettingsToCustomer */
        $settingsForDealer = $this->getConcreteRepository()->createOrUpdate([
            'dealer_id' => $setting->dealer_id,
            'settings' => $settings
        ]);

        // Then I should get a class which is an instance of Settings
        self::assertInstanceOf(\Illuminate\Support\Collection::class, $settingsForDealer);

        // Setting did not exist before but does now after create
        self::assertSame(count($created) + count($missing), Settings::where(['dealer_id' => $dealerId])->count());
    }

    /**
     * Examples of parameters with expected total.
     *
     * @return array[]
     */
    public function validQueryParametersProvider(): array
    {
        $dealerIdLambda = static function (SettingsSeeder $seeder) {
            return $seeder->dealer->getKey();
        };

        $settingNameLambda = static function (SettingsSeeder $seeder): string {
            $settings = $seeder->createdSettings;
            return $settings[array_rand($settings, 1)]->setting;
        };

        return [                 // array $parameters, int $expectedTotal
            'By dummy dealer' => [['dealer_id' => $dealerIdLambda], 4],
            'By dummy dealer\'s setting name' => [['dealer_id' => $dealerIdLambda, 'setting' => $settingNameLambda], 1],
        ];
    }

    /**
     * Examples of parameters with expected total.
     *
     * @return array[]
     */
    public function validFindParametersProvider(): array
    {
        $dealerIdLambda = static function (SettingsSeeder $seeder) {
            return $seeder->dealer->getKey();
        };

        $settingNameLambda = static function (SettingsSeeder $seeder): string {
            $settings = $seeder->createdSettings;
            return $settings[array_rand($settings, 1)]->setting;
        };

        return [                                // array $parameters, int $expectedTotal
            'By dummy dealer\'s setting name' => [['dealer_id' => $dealerIdLambda, 'setting' => $settingNameLambda], 1],
        ];
    }


    public function setUp(): void
    {
        parent::setUp();

        $this->seeder = new SettingsSeeder();
    }

    public function tearDown(): void
    {
        $this->seeder->cleanUp();

        parent::tearDown();
    }

    /**
     * @return SettingsRepositoryInterface
     *
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     *
     */
    protected function getConcreteRepository(): SettingsRepositoryInterface
    {
        return $this->app->make(SettingsRepositoryInterface::class);
    }
}
