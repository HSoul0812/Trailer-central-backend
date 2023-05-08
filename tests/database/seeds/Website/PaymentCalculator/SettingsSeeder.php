<?php

namespace Tests\database\seeds\Website\PaymentCalculator;

use App\Models\Website\PaymentCalculator\Settings;
use App\Traits\WithGetter;
use Tests\database\seeds\Seeder;

/**
 * Class SettingsSeeder
 * @package Tests\database\seeds\Website\PaymentCalculator
 *
 * @property-read Settings $settings
 */
class SettingsSeeder extends Seeder
{
    use WithGetter;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var array
     */
    private $settingsParams;

    public function __construct(array $params = [])
    {
        $this->settingsParams = $params['settingsParams'] ?? [];
    }

    public function seed(): void
    {
        $this->settings = factory(Settings::class)->create($this->settingsParams);
    }

    public function cleanUp(): void
    {
        Settings::query()->where('id', '=', $this->settings->getKey())->delete();
    }
}
