<?php

namespace Tests\Feature\Api\Home;

use Tests\Common\FeatureTestCase;

/**
 * @covers \App\Http\Controllers\v1\Home\HomeController
 */
class HomeTest extends FeatureTestCase
{
    /**
     * @covers ::index
     */
    public function testIndex(): void
    {
        $response = $this->get('/api');

        $response->assertStatus(204);
    }
}
