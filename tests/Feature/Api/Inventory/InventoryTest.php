<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Parts;

use Tests\Common\FeatureTestCase;

class InventoryTest extends FeatureTestCase
{
    public function testIndexNoInteger(): void
    {

        $response = $this->get('/api/inventory/1.1');

        $json = json_decode($response->getContent(), true);

        $response->assertStatus(404);
    }
    
    public function testIndexInvalidId(): void
    {

        $response = $this->get('/api/inventory/0');

        $json = json_decode($response->getContent(), true);

        $response->assertStatus(422);
    }
}