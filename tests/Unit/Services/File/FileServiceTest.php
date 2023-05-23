<?php


namespace Tests\Unit\Services\File;

use App\Exceptions\File\FileUploadException;
use App\Helpers\SanitizeHelper;
use App\Services\File\DTOs\FileDto;
use App\Services\File\FileService;
use App\Services\Integration\Common\DTOs\AttachmentFile;
use GuzzleHttp\Client;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Mockery\LegacyMockInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

/**
 * @group DW
 * @group DW_INVENTORY
 *
 * Test for App\Services\File\FileService
 *
 * Class FileServiceTest
 * @package Tests\Unit\Services\File
 *
 * @coversDefaultClass \App\Services\File\FileService
 */
class FileServiceTest extends TestCase
{
    private const TEST_DEALER_ID = PHP_INT_MAX - 100500 + 1;
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
    private $file;

    public function setUp(): void
    {
        parent::setUp();

        $this->httpClient = Mockery::mock(Client::class);
        $this->app->instance(Client::class, $this->httpClient);

        $this->sanitizeHelper = Mockery::mock(SanitizeHelper::class);
        $this->app->instance(SanitizeHelper::class, $this->sanitizeHelper);

        $this->httpStream = Mockery::mock(StreamInterface::class);
        $this->httpResponse = Mockery::mock(ResponseInterface::class);

        $this->file = Storage::disk('test_resources')->get('test_file.txt');

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
            ->shouldReceive('getStatusCode')
            ->once()
            ->andReturn(Response::HTTP_OK);

        $this->httpResponse
            ->shouldReceive('getBody')
            ->once()
            ->with()
            ->andReturn($this->httpStream);

        $this->httpStream
            ->shouldReceive('getContents')
            ->once()
            ->with()
            ->andReturn($this->file);

        $this->sanitizeHelper
            ->shouldReceive('cleanFilename')
            ->passthru();

        $imageService = app()->make(FileService::class);
        $result = $imageService->upload($url, $title, $dealerId);

        $this->assertInstanceOf(FileDto::class, $result);
        $this->assertNotNull($result->getPath());
        $this->assertNotNull($result->getMimeType());

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
            ->shouldReceive('getStatusCode')
            ->once()
            ->andReturn(Response::HTTP_OK);

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

        $imageService = app()->make(FileService::class);
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
            ->shouldReceive('getStatusCode')
            ->once()
            ->andReturn(Response::HTTP_OK);

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

        $imageService = app()->make(FileService::class);
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
        $fileService = app()->make(FileService::class);
        $file = UploadedFile::fake()->create('test.pdf', '1000', 'application/pdf');

        $result = $fileService->uploadLocal(['file' => $file]);

        $this->assertInstanceOf(FileDto::class, $result);

        $this->assertNotNull($result->getMimeType());
        $this->assertNotNull($result->getPath());
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
    public function testUploadLocalByAttachmentFile()
    {
        $fileService = app()->make(FileService::class);
        $file = UploadedFile::fake()->create('test.pdf', '1000', 'application/pdf');

        $attachmentFile = new AttachmentFile();
        $attachmentFile->setContents($file->get());
        $attachmentFile->setMimeType($file->getMimeType());

        $result = $fileService->uploadLocal(['file' => $attachmentFile]);

        $this->assertInstanceOf(FileDto::class, $result);

        $this->assertNotNull($result->getMimeType());
        $this->assertNotNull($result->getPath());
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

        $fileService = app()->make(FileService::class);
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

        $fileService = app()->make(FileService::class);
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

        $fileService = app()->make(FileService::class);
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
