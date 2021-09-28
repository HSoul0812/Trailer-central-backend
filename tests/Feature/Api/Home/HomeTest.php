<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Home;

use Tests\Common\FeatureTestCase;

class HomeTest extends FeatureTestCase
{
    public function testIndex(): void
    {
        $response = $this->get('/api');

        $response->assertStatus(204);
    }
}
