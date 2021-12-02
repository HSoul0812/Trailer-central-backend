<?php

namespace Tests\Integration;

use Illuminate\Foundation\Testing\TestResponse;
use Tests\TestCase;

/**
 * Class IntegrationTestCase
 * @package Tests\Integration
 */
abstract class IntegrationTestCase extends TestCase
{
    /**
     * @param TestResponse $response
     */
    protected function assertEmptyResponseData(TestResponse $response)
    {
        $responseJson = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('data', $responseJson);
        $this->assertEmpty( $responseJson['data']);

        $this->assertArrayHasKey('meta', $responseJson);
        $this->assertArrayHasKey('pagination', $responseJson['meta']);
        $this->assertArrayHasKey('total', $responseJson['meta']['pagination']);
        $this->assertSame(0, $responseJson['meta']['pagination']['total']);
        $this->assertArrayHasKey('count', $responseJson['meta']['pagination']);
        $this->assertSame(0, $responseJson['meta']['pagination']['count']);
        $this->assertArrayHasKey('current_page', $responseJson['meta']['pagination']);
        $this->assertSame(1, $responseJson['meta']['pagination']['current_page']);
        $this->assertArrayHasKey('total_pages', $responseJson['meta']['pagination']);
        $this->assertSame(1, $responseJson['meta']['pagination']['total_pages']);
    }

    /**
     * @param TestResponse $response
     * @param array $expectedData
     * @param bool $isCollection
     */
    protected function assertResponseDataEquals(TestResponse $response, array $expectedData, bool $isCollection = true)
    {
        $responseJson = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('data', $responseJson);
        $this->assertNotEmpty($responseJson['data']);

        $responseItem = $isCollection ? $responseJson['data'][0] : $responseJson['data'];

        foreach ($expectedData as $expectedDataKey => $expectedDataValue) {
            $this->assertArrayHasKey($expectedDataKey, $responseItem);
            $this->assertEquals($expectedDataValue, $responseItem[$expectedDataKey]);
        }

        if ($isCollection) {
            $this->assertArrayHasKey('meta', $responseJson);
            $this->assertArrayHasKey('pagination', $responseJson['meta']);
            $this->assertArrayHasKey('total', $responseJson['meta']['pagination']);
            $this->assertSame(1, $responseJson['meta']['pagination']['total']);
            $this->assertArrayHasKey('count', $responseJson['meta']['pagination']);
            $this->assertSame(1, $responseJson['meta']['pagination']['count']);
            $this->assertArrayHasKey('current_page', $responseJson['meta']['pagination']);
            $this->assertSame(1, $responseJson['meta']['pagination']['current_page']);
            $this->assertArrayHasKey('total_pages', $responseJson['meta']['pagination']);
            $this->assertSame(1, $responseJson['meta']['pagination']['total_pages']);
        }
    }

    /**
     * @param TestResponse $response
     * @param bool $withData
     */
    protected function assertUpdateResponse(TestResponse $response, bool $withData = true)
    {
        $responseJson = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('response', $responseJson);
        $this->assertArrayHasKey('status', $responseJson['response']);
        $this->assertSame('success', $responseJson['response']['status']);

        if ($withData) {
            $this->assertArrayHasKey('data', $responseJson['response']);
            $this->assertArrayHasKey('id', $responseJson['response']['data']);
        }
    }

    /**
     * @param TestResponse $response
     * @param bool $withData
     */
    protected function assertCreateResponse(TestResponse $response, bool $withData = true)
    {
        $responseJson = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('response', $responseJson);
        $this->assertArrayHasKey('status', $responseJson['response']);
        $this->assertSame('success', $responseJson['response']['status']);

        if ($withData) {
            $this->assertArrayHasKey('data', $responseJson['response']);
            $this->assertArrayHasKey('id', $responseJson['response']['data']);
        }
    }

    /**
     * @param TestResponse $response
     * @return int
     */
    protected function getResponseId(TestResponse $response): int
    {
        $responseJson = json_decode($response->getContent(), true);
        return $responseJson['response']['data']['id'];
    }
}
