<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Parts;

use Database\Seeders\Parts\CategoryAndTypeSeeder;
use Tests\Common\FeatureTestCase;

class TypeTest extends FeatureTestCase
{
    public function testIndex(): void
    {
        $seeder = new CategoryAndTypeSeeder();
        $seeder->run();

        $response = $this->get('/api/parts/types');

        $json = json_decode($response->getContent(), true);

        self::assertIsArray($json['data']);
        $response->assertStatus(200);
    }
}
