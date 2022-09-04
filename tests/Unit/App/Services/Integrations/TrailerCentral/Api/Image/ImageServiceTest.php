<?php

namespace Tests\Unit\App\Services\Integrations\TrailerCentral\Api\Image;

use App\Services\Integrations\TrailerCentral\Api\Image\ImageService;
use App\Services\Integrations\TrailerCentral\Api\Image\ImageServiceInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Storage;
use Tests\Common\TestCase;

class ImageServiceTest extends TestCase
{
    private ImageServiceInterface $service;
    private array $container;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = $this->getConcreteService();
    }

    public function testUploadImage() {
        $response = $this->service->uploadImage(1001, Storage::path('koala.png'));
        $this->assertEquals($this->container[0]['request']->getMethod(), 'POST');
        $this->assertEquals($this->container[0]['request']->getUri()->getPath(), '/api/images/local');
        $this->assertEquals([
            'data' => [
                "url" => "https://api-v1.zhao.dev.trailercentral.com/storage/tmp/media/eQNHLd/GGoOHI/7wQz0Sxumbsb.png"
            ]
        ], $response);
    }

    private function getConcreteService(): ImageServiceInterface
    {
        $httpClient = $this->mockHttpClient();
        return new ImageService($httpClient);
    }

    private function mockHttpClient(): Client {
        $mockData = '{
          "data": {
            "url": "https:\/\/api-v1.zhao.dev.trailercentral.com\/storage\/tmp\/media\/eQNHLd\/GGoOHI\/7wQz0Sxumbsb.png"
          }
        }';
        $mock = new MockHandler([
            new Response(200, [], $mockData),
        ]);

        $this->container = [];
        $history = Middleware::history($this->container);
        $stack = HandlerStack::create($mock);
        $stack->push($history);

        return new Client(['handler' => $stack]);
    }
}
