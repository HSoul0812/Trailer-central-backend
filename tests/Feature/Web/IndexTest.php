<?php

declare(strict_types=1);

namespace Tests\Feature\Web;

use Tests\Common\FeatureTestCase;

class IndexTest extends FeatureTestCase
{
    /**
     * @coversNothing
     */
    public function testIndex(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
