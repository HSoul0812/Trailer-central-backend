<?php

namespace Tests\Unit\Models\Dms;

use App\Models\CRM\Dms\Settings;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    /**
     * @group DMS
     * @group DMS_SETTINGS
     *
     * @return void
     */
    public function testMetaCanSaveByDotNotation()
    {
        /** @var Settings $settings */
        /** @var Settings $settings2 */
        $settings = Settings::where('dealer_id', 1001)->get()->first();

        $testKey = 'testKey1.testKey2';
        $testValue = uniqid();

        $settings->setByName('meta', $testKey, $testValue);
        $settings->save();

        $settings2 = Settings::where('dealer_id', 1001)->get()->first();
        $this->assertSame($testValue, $settings2->getByName('meta', $testKey));

    }
}
