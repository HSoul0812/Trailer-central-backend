<?php

namespace Tests\Feature\Api\Page;

use Tests\Common\FeatureTestCase;

class PageTest extends FeatureTestCase
{

    /**
     * Tests if the api endpoint is available
     *
     * @return void
     */
    public function testIndex(): void
    {
        $response = $this->get('/api/page');

        $json = json_decode($response->getContent(), true);

        self::assertIsArray($json['data']);
        $response->assertStatus(200);
    }

    /**
     * Tests if the data returned is in the expected structure
     *
     * @return void
     */
    public function testIndexStructure(): void
    {
        $response = $this->get('/api/page');

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'url',
                    'description'
                ]
            ]
        ]);
    }
}
