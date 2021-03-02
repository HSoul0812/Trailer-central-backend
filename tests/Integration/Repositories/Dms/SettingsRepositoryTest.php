<?php

namespace Tests\Integration\Repositories\Dms;

use App\Models\CRM\Dms\Settings;
use App\Repositories\Dms\SettingsRepository;
use Tests\TestCase;

class SettingsRepositoryTest extends TestCase
{
    public function testGetByDealerIdReturnsDmsSettings()
    {
        /** @var SettingsRepository $repository */
        $repository = app(SettingsRepository::class);
        $settings = $repository->getByDealerId(1001);

        $this->assertInstanceOf(Settings::class, $settings);
    }

    public function testGetByDealerIdReturnsCorrectDealerSetting()
    {
        /** @var SettingsRepository $repository */
        $repository = app(SettingsRepository::class);
        $settings = $repository->getByDealerId(1001);

        $this->assertSame(1001, $settings->dealer_id);
    }
}
