<?php

namespace Tests\Feature\Api\Page;

use Tests\Common\FeatureTestCase;

class PageTest extends FeatureTestCase
{
    /**
     * @var string
     */
    protected string $endpoint = '/api/pages';

    /**
     * @var array
     */
    protected array $dataStructure = [
        'data' => [
            '*' => [
                'id',
                'name',
                'url'
            ]
        ]
    ];

    /**
     * Tests if the api endpoint is available
     *
     * @return void
     */
    public function testIndex(): void
    {
        $response = $this->get($this->endpoint);
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
        $response = $this->get($this->endpoint);
        $response->assertJsonStructure($this->dataStructure);
    }

    /**
     * Tests if the data returned is the expected data
     *
     * @return void
     */
    public function testIndexData(): void
    {
        $response = $this->get($this->endpoint);
        $json = json_decode($response->getContent(), true);

        foreach ($json["data"] as $page) {
            $this->assertDatabaseHas('pages', $page);
        }
    }
}
