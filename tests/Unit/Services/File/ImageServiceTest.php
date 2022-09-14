<?php

namespace Tests\Unit\Services\File;

use App\Exceptions\File\FileUploadException;
use App\Exceptions\File\ImageUploadException;
use App\Helpers\ImageHelper;
use App\Helpers\SanitizeHelper;
use App\Services\File\DTOs\FileDto;
use App\Services\File\ImageService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Mockery\LegacyMockInterface;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Tests\TestCase;

/**
 * Test for App\Services\File\ImageService
 *
 * Class ImageServiceTest
 * @package Tests\Unit\File
 *
 * @coversDefaultClass \App\Services\File\ImageService
 */
class ImageServiceTest extends TestCase
{
    private const TEST_DEALER_ID = PHP_INT_MAX - 100500;
    private const TEST_TITLE = 'test_image_title';
    private const TEST_URL = 'test_image_url';

    /**
     * @var LegacyMockInterface|Client
     */
    private $httpClient;

    /**
     * @var LegacyMockInterface|SanitizeHelper
     */
    private $sanitizeHelper;

    /**
     * @var LegacyMockInterface|ImageHelper
     */
    private $imageHelper;

    /**
     * @var LegacyMockInterface|ResponseInterface
     */
    private $httpResponse;

    /**
     * @var LegacyMockInterface|StreamInterface
     */
    private $httpStream;

    /**
     * @var resource
     */
    private $image;

    public function setUp(): void
    {
        parent::setUp();

        $this->httpClient = Mockery::mock(Client::class);
        $this->app->instance(Client::class, $this->httpClient);

        $this->sanitizeHelper = Mockery::mock(SanitizeHelper::class);
        $this->app->instance(SanitizeHelper::class, $this->sanitizeHelper);

        $this->imageHelper = Mockery::mock(ImageHelper::class);
        $this->app->instance(ImageHelper::class, $this->imageHelper);

        $this->httpStream = Mockery::mock(StreamInterface::class);
        $this->httpResponse = Mockery::mock(ResponseInterface::class);

        $this->image = Storage::disk('test_resources')->get('test_image.jpeg');

        Storage::fake('s3');
        Storage::fake('local_tmp');
    }

    public function tearDown(): void
    {
        Storage::fake('s3');
        Storage::fake('local_tmp');

        parent::tearDown();
    }

    /**
     * @covers ::upload
     * @dataProvider uploadDataProvider
     *
     * @group DMS
     * @group DMS_FILES
     *
     * @param string $url
     * @param string $title
     * @param int $dealerId
     * @throws FileUploadException
     * @throws ImageUploadException
     * @throws BindingResolutionException
     */
    public function testUpload(string $url, string $title, int $dealerId)
    {
        $this->httpClient
            ->shouldReceive('get')
            ->once()
            ->with(self::TEST_URL, ['http_errors' => false])
            ->andReturn($this->httpResponse);

        $this->httpResponse
            ->shouldReceive('getBody')
            ->once()
            ->with()
            ->andReturn($this->httpStream);

        $this->httpStream
            ->shouldReceive('getContents')
            ->once()
            ->with()
            ->andReturn($this->image);

        $this->sanitizeHelper
            ->shouldReceive('cleanFilename')
            ->passthru();

        $this->imageHelper
            ->shouldReceive('resize')
            ->passthru();

        $imageService = app()->make(ImageService::class);
        $result = $imageService->upload($url, $title, $dealerId);

        $this->assertInstanceOf(FileDto::class, $result);
        $this->assertNotNull($result->getPath());
        $this->assertNotNull($result->getHash());

        Storage::disk('s3')->assertExists($result->getPath());
    }

    /**
     * @covers ::upload
     * @dataProvider uploadDataProvider
     *
     * @group DMS
     * @group DMS_FILES
     *
     * @param string $url
     * @param string $title
     * @param int $dealerId
     * @throws BindingResolutionException
     * @throws FileUploadException
     * @throws ImageUploadException
     */
    public function testUploadWithOverlay(string $url, string $title, int $dealerId)
    {
        $params = [
            'overlayText' => 'some_text'
        ];

        $this->httpClient
            ->shouldReceive('get')
            ->once()
            ->with(self::TEST_URL, ['http_errors' => false])
            ->andReturn($this->httpResponse);

        $this->httpResponse
            ->shouldReceive('getBody')
            ->once()
            ->with()
            ->andReturn($this->httpStream);

        $this->httpStream
            ->shouldReceive('getContents')
            ->once()
            ->with()
            ->andReturn($this->image);

        $this->sanitizeHelper
            ->shouldReceive('cleanFilename')
            ->passthru();

        $this->imageHelper
            ->shouldReceive('resize')
            ->passthru();

        $this->imageHelper
            ->shouldReceive('addOverlay')
            ->with(\Mockery::type('string'), 'some_text')
            ->once();

        $imageService = app()->make(ImageService::class);
        $result = $imageService->upload($url, $title, $dealerId, null, $params);

        $this->assertInstanceOf(FileDto::class, $result);
        $this->assertNotNull($result->getPath());
        $this->assertNotNull($result->getHash());

        Storage::disk('s3')->assertExists($result->getPath());
    }

    /**
     * @covers ::upload
     * @dataProvider uploadDataProvider
     *
     * @group DMS
     * @group DMS_FILES
     *
     * @param string $url
     * @param string $title
     * @param int $dealerId
     * @throws BindingResolutionException
     * @throws FileUploadException
     * @throws ImageUploadException
     * @throws FileNotFoundException
     */
    public function testUploadWithWrongExtension(string $url, string $title, int $dealerId)
    {
        $this->expectException(ImageUploadException::class);

        $image = Storage::disk('test_resources')->get('test_image.bmp');

        $this->httpClient
            ->shouldReceive('get')
            ->once()
            ->with(self::TEST_URL, ['http_errors' => false])
            ->andReturn($this->httpResponse);

        $this->httpResponse
            ->shouldReceive('getBody')
            ->once()
            ->with()
            ->andReturn($this->httpStream);

        $this->httpStream
            ->shouldReceive('getContents')
            ->once()
            ->with()
            ->andReturn($image);

        $imageService = app()->make(ImageService::class);
        $imageService->upload($url, $title, $dealerId);
    }

    /**
     * @covers ::upload
     * @dataProvider uploadDataProvider
     *
     * @group DMS
     * @group DMS_FILES
     *
     * @param string $url
     * @param string $title
     * @param int $dealerId
     * @throws BindingResolutionException
     * @throws FileUploadException
     * @throws ImageUploadException
     */
    public function testUploadEmptyContent(string $url, string $title, int $dealerId)
    {
        $this->expectException(FileUploadException::class);

        $this->httpClient
            ->shouldReceive('get')
            ->once()
            ->with(self::TEST_URL, ['http_errors' => false])
            ->andReturn($this->httpResponse);

        $this->httpResponse
            ->shouldReceive('getBody')
            ->once()
            ->with()
            ->andReturn($this->httpStream);

        $this->httpStream
            ->shouldReceive('getContents')
            ->once()
            ->with()
            ->andReturn(null);

        $imageService = app()->make(ImageService::class);
        $imageService->upload($url, $title, $dealerId);
    }

    /**
     * @covers ::upload
     * @dataProvider uploadDataProvider
     *
     * @group DMS
     * @group DMS_FILES
     *
     * @param string $url
     * @param string $title
     * @param int $dealerId
     * @throws BindingResolutionException
     * @throws FileUploadException
     * @throws ImageUploadException
     */
    public function testUploadEmptyContentSkip(string $url, string $title, int $dealerId)
    {
        $params = [
            'skipNotExisting' => true
        ];

        $this->httpClient
            ->shouldReceive('get')
            ->once()
            ->with(self::TEST_URL, ['http_errors' => false])
            ->andReturn($this->httpResponse);

        $this->httpResponse
            ->shouldReceive('getBody')
            ->once()
            ->with()
            ->andReturn($this->httpStream);

        $this->httpStream
            ->shouldReceive('getContents')
            ->once()
            ->with()
            ->andReturn(null);

        $imageService = app()->make(ImageService::class);
        $result = $imageService->upload($url, $title, $dealerId, null, $params);

        $this->assertNull($result);
    }

    /**
     * @covers ::uploadLocal
     *
     * @group DMS
     * @group DMS_FILES
     */
    public function testUploadLocal()
    {
        $fileService = app()->make(ImageService::class);
        $file = UploadedFile::fake()->image('test.png');

        $result = $fileService->uploadLocal(['file' => $file]);

        $this->assertInstanceOf(FileDto::class, $result);

        $this->assertNotNull($result->getPath());
        $this->assertNotNull($result->getHash());
        $this->assertNotNull($result->getUrl());

        $path = str_replace(Storage::disk('local_tmp')->path(''),'', $result->getPath());

        Storage::disk('local_tmp')->assertExists($path);
    }

    /**
     * @covers ::uploadLocal
     *
     * @group DMS
     * @group DMS_FILES
     */
    public function testUploadLocalWrongMimeType()
    {
        $this->expectException(FileUploadException::class);

        $fileService = app()->make(ImageService::class);
        $file = UploadedFile::fake()->create('test.php', '1000', 'application/x-httpd-php');

        $fileService->uploadLocal(['file' => $file]);
    }

    /**
     * @covers ::uploadLocal
     *
     * @group DMS
     * @group DMS_FILES
     */
    public function testUploadLocalWithoutFile()
    {
        $this->expectException(FileUploadException::class);

        $fileService = app()->make(ImageService::class);
        $fileService->uploadLocal([]);
    }

    /**
     * @covers ::uploadLocal
     *
     * @group DMS
     * @group DMS_FILES
     */
    public function testUploadLocalWrongFile()
    {
        $this->expectException(FileUploadException::class);

        $fileService = app()->make(ImageService::class);
        $fileService->uploadLocal(['file' => 'wrong_file']);
    }


    /**
     * @return array[]
     */
    public function uploadDataProvider(): array
    {
        return [[
            self::TEST_URL,
            self::TEST_TITLE,
            self::TEST_DEALER_ID,
        ]];
    }
}
