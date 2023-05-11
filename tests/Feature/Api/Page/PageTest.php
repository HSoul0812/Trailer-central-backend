<?php

namespace Tests\Feature\Api\Page;

use Tests\Common\FeatureTestCase;

class PageTest extends FeatureTestCase
{
    protected string $endpoint = '/api/pages';

    protected array $dataStructure = [
        'data' => [
            '*' => [
                'id',
                'name',
                'url',
            ],
        ],
    ];

    /**
     * Tests if the api endpoint is available.
     */
    public function testIndex(): void
    {
        $response = $this->get($this->endpoint);
        $json = json_decode($response->getContent(), true);

        self::assertIsArray($json['data']);
        $response->assertStatus(200);
    }

    /**
     * Tests if the data returned is in the expected structure.
     */
    public function testIndexStructure(): void
    {
        $response = $this->get($this->endpoint);
        $response->assertJsonStructure($this->dataStructure);
    }

    /**
     * Tests if the data returned is the expected data.
     */
    public function testIndexData(): void
    {
        $response = $this->get($this->endpoint);
        $json = json_decode($response->getContent(), true);

        foreach ($json['data'] as $page) {
            $this->assertDatabaseHas('pages', $page);
        }
    }

    /**
     * Tests if the data returned is with an expected encoding.
     */
    public function testIndexPagesEnconding(): void
    {
        $response = $this->get($this->endpoint);
        $json = json_decode($response->getContent(), true);

        foreach ($json['data'] as $page) {
            foreach ($page as $key => $value) {
                $this->assertEquals(
                    'UTF-8',
                    mb_detect_encoding(
                        $key,
                        'UTF-8'
                    )
                );

                $this->assertEquals(
                    'UTF-8',
                    mb_detect_encoding(
                        $value,
                        'UTF-8'
                    )
                );
            }
        }
    }
}
