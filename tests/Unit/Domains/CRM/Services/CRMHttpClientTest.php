<?php

namespace Tests\Unit\Domains\CRM\Services;

use App\Domains\CRM\Services\CRMHttpClient;
use Tests\TestCase;

class CRMHttpClientTest extends TestCase
{
    /**
     * @group DMS
     * @group DMS_CRM_CLIENT
     *
     * @return void
     */
    public function testItCanBeInitialized()
    {
        $client = new CRMHttpClient();

        $this->assertEquals(config('app.new_design_crm_url'), $client->getConfig('base_uri'));
    }

    /**
     * @group DMS
     * @group DMS_CRM_CLIENT
     *
     * @return void
     */
    public function testItAllowsConfigOverride()
    {
        $client = new CRMHttpClient([
            'base_uri' => 'https://google.com',
        ]);

        $this->assertEquals('https://google.com', $client->getConfig('base_uri'));
    }
}
