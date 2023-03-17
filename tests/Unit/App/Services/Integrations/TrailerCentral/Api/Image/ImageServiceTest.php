<?php

namespace Tests\Unit\App\Services\Integrations\TrailerCentral\Api\Image;

use App\Domains\Images\Actions\DeleteOldLocalImagesAction;
use App\Repositories\Integrations\TrailerCentral\AuthTokenRepository;
use App\Services\Integrations\TrailerCentral\Api\Image\ImageService;
use App\Services\Integrations\TrailerCentral\Api\Image\ImageServiceInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Mockery\MockInterface;
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

    public function testUploadImage()
    {
        $this->markTestSkipped("This test is skipped because it requires TC");

        $response = $this->service->uploadImage(1001, Storage::path('koala.png'));
        $this->assertEquals($this->container[0]['request']->getMethod(), 'POST');
        $this->assertEquals($this->container[0]['request']->getUri()->getPath(), '/api/images/local');
        $this->assertEquals([
            'data' => [
                "url" => "https://api-v1.zhao.dev.trailercentral.com/storage/tmp/media/eQNHLd/GGoOHI/7wQz0Sxumbsb.png"
            ]
        ], $response);
    }

    public function testItCanDeleteOldLocalImages()
    {
        $olderThanDays = 20;

        $this->instance(
            abstract: DeleteOldLocalImagesAction::class,
            instance: Mockery::mock(DeleteOldLocalImagesAction::class, function (MockInterface $mock) use ($olderThanDays) {
                $mock->shouldReceive('execute')->with($olderThanDays)->once()->andReturns();
            })
        );

        $service = $this->getConcreteService();

        $service->deleteOldLocalImages($olderThanDays);
    }

    private function getConcreteService(): ImageServiceInterface
    {
        $httpClient = $this->mockHttpClient();

        $authRepo = new AuthTokenRepository();

        $deleteOldLocalImagesAction = resolve(DeleteOldLocalImagesAction::class);

        return new ImageService($httpClient, $authRepo, $deleteOldLocalImagesAction);
    }

    private function mockHttpClient(): Client
    {
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
