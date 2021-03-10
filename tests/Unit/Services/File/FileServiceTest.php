<?php


namespace Tests\Unit\Services\File;

use App\Exceptions\File\FileUploadException;
use App\Helpers\SanitizeHelper;
use App\Services\File\DTOs\FileDto;
use App\Services\File\FileService;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Mockery\LegacyMockInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Tests\TestCase;

/**
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
     * @param string $url
     * @param string $title
     * @param int $dealerId
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
     * @param string $url
     * @param string $title
     * @param int $dealerId
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

        $imageService = app()->make(FileService::class);
        $imageService->upload($url, $title, $dealerId);
    }

    /**
     * @covers ::upload
     * @dataProvider uploadDataProvider
     *
     * @param string $url
     * @param string $title
     * @param int $dealerId
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

        $imageService = app()->make(FileService::class);
        $result = $imageService->upload($url, $title, $dealerId, null, $params);

        $this->assertNull($result);
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
