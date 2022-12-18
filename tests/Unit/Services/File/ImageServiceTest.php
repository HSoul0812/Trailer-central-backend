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
use App\Models\User\User;
use App\Models\Inventory\Inventory;
use Imagick;

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
    private const TEST_DEALER_NAME = 'TEST_DEALER_NAME';
    private const TEST_DEALER_LOCATION = 'TEST_DEALER_LOCATION';
    private const TEST_DEALER_PHONE_NUMBER = 'TEST_DEALER_PHONE_NUMBER';
    private const TEST_INVENTORY_ID = PHP_INT_MAX - 100500;

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

        $this->imageHelper = Mockery::mock(ImageHelper::class)->makePartial();
        $this->app->instance(ImageHelper::class, $this->imageHelper);

        $this->httpStream = Mockery::mock(StreamInterface::class);
        $this->httpResponse = Mockery::mock(ResponseInterface::class);

        $this->image = Storage::disk('test_resources')->get('test_image.jpeg');

        Storage::fake('s3');
        Storage::fake('local_tmp');
        Storage::fake('tmp');
    }

    public function tearDown(): void
    {
        Storage::fake('s3');
        Storage::fake('local_tmp');
        Storage::fake('tmp');

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

    /**
     * @return array[]
     */
    public function overlayParamDataProvider()
    {
        return [[[
                    'dealer_id' => self::TEST_DEALER_ID,
                    'inventory_id' => self::TEST_INVENTORY_ID,
                    'overlay_logo' => '',
                    'overlay_logo_position' => User::OVERLAY_LOGO_POSITION_LOWER_RIGHT, 
                    'overlay_logo_width' => '20%', 
                    'overlay_logo_height' => '20%', 
                    'overlay_upper' => User::OVERLAY_UPPER_DEALER_NAME, 
                    'overlay_upper_bg' => '#000000', 
                    'overlay_upper_alpha' => 0, 
                    'overlay_upper_text' => '#ffffff', 
                    'overlay_upper_size' => 40, 
                    'overlay_upper_margin' => 40,
                    'overlay_lower' => User::OVERLAY_UPPER_DEALER_PHONE, 
                    'overlay_lower_bg' => '#000000', 
                    'overlay_lower_alpha' => 0, 
                    'overlay_lower_text' => '#ffffff', 
                    'overlay_lower_size' => 40, 
                    'overlay_lower_margin' => 40,
                    'overlay_enabled' => Inventory::OVERLAY_ENABLED_ALL,
                    'dealer_overlay_enabled' => Inventory::OVERLAY_ENABLED_ALL,
                    'overlay_text_dealer' => self::TEST_DEALER_NAME,
                    'overlay_text_phone' => self::TEST_DEALER_PHONE_NUMBER,
                    'overlay_text_location' => self::TEST_DEALER_LOCATION,
                ]]];
    }

    /**
     * @dataProvider overlayParamDataProvider
     * @group overlay_images
     */
    public function testAddUpperTextLowerLogoOverlays($overlayParams)
    {
        $overlayParams['overlay_logo'] = Storage::disk('test_resources')->path('logo_image.png');

        $imagePath = Storage::disk('test_resources')->path('inventory_image.png');

        $this->imageHelper->shouldReceive('addLogoOverlay')
            ->withArgs([
                Storage::disk('tmp')->path('tmp_addUpperTextOverlay'),
                Storage::disk('test_resources')->path('logo_image.png'),
                $overlayParams
            ])
            ->passthru();

        $this->imageHelper->shouldReceive('addUpperTextOverlay')
            ->withArgs([$imagePath, self::TEST_DEALER_NAME, $overlayParams])
            ->passthru();

        $this->imageHelper->shouldNotReceive('addLowerTextOverlay');

        // Mock Temp Filenames
        $this->imageHelper
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('getRandomString')
            ->andReturn(
                'tmp_addUpperTextOverlay',
                'tmp_localLogoPath',
                'tmp_resizedLogo',
                'tmp_localImagePath',
                'tmp_addLogoOverlay'
            );

        $imageService = app()->make(ImageService::class);

        $newImage = $imageService->addOverlays($imagePath, $overlayParams);

        Storage::disk('tmp')->assertExists([
            'tmp_addLogoOverlay'
        ]);

        Storage::disk('tmp')->assertMissing([
            'tmp_addUpperTextOverlay',
            'tmp_localLogoPath',
            'tmp_resizedLogo',
            'tmp_localImagePath',
        ]);

        $this->assertEquals(mime_content_type($newImage), 'image/png');

        // to compare the image manually by human
        // Storage::disk('test_resources')->put('testAddUpperTextLowerLogoOverlays.png', 
        //     file_get_contents($newImage));

        $this->assertImages($newImage,
            Storage::disk('test_resources')->path('testAddUpperTextLowerLogoOverlays.png'));
    }

    /**
     * @dataProvider overlayParamDataProvider
     * @group overlay_images
     */
    public function testAddLowerTextUpperLogoOverlays($overlayParams)
    {
        $overlayParams['overlay_logo'] = Storage::disk('test_resources')->path('logo_image.png');
        $overlayParams['overlay_logo_position'] = User::OVERLAY_LOGO_POSITION_UPPER_RIGHT;

        $imagePath = Storage::disk('test_resources')->path('inventory_image.png');
            
        $this->imageHelper->shouldReceive('addLogoOverlay')
            ->withArgs([
                Storage::disk('tmp')->path('tmp_addLowerTextOverlay'),
                Storage::disk('test_resources')->path('logo_image.png'),
                $overlayParams
            ])
            ->passthru();

        $this->imageHelper->shouldReceive('addLowerTextOverlay')
            ->withArgs([$imagePath, self::TEST_DEALER_PHONE_NUMBER, $overlayParams])
            ->passthru();

        $this->imageHelper->shouldNotReceive('addUpperTextOverlay');

        // Mock Temp Filenames
        $this->imageHelper
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('getRandomString')
            ->andReturn(
                'tmp_addLowerTextOverlay',
                'tmp_localLogoPath',
                'tmp_resizedLogo',
                'tmp_localImagePath',
                'tmp_addLogoOverlay'
            );

        $imageService = app()->make(ImageService::class);

        $newImage = $imageService->addOverlays($imagePath, $overlayParams);

        Storage::disk('tmp')->assertExists([
            'tmp_addLogoOverlay'
        ]);

        Storage::disk('tmp')->assertMissing([
            'tmp_addLowerTextOverlay',
            'tmp_localLogoPath',
            'tmp_resizedLogo',
            'tmp_localImagePath',
        ]);

        $this->assertEquals(mime_content_type($newImage), 'image/png');

        // to compare the image manually by human
        // Storage::disk('test_resources')->put('testAddLowerTextUpperLogoOverlays.png', 
        //     file_get_contents($newImage));

        $this->assertImages($newImage,
            Storage::disk('test_resources')->path('testAddLowerTextUpperLogoOverlays.png'));
    }

    /**
     * @dataProvider overlayParamDataProvider
     * @group overlay_images
     */
    public function testAddLowerTextUpperTextOverlays($overlayParams)
    {
        $overlayParams['overlay_logo'] = Storage::disk('test_resources')->path('logo_image.png');
        $overlayParams['overlay_logo_position'] = User::OVERLAY_LOGO_POSITION_NONE;

        $imagePath = Storage::disk('test_resources')->path('inventory_image.png');
            
        $this->imageHelper->shouldReceive('addUpperTextOverlay')
            ->withArgs([$imagePath, self::TEST_DEALER_NAME, $overlayParams])
            ->passthru();

        $this->imageHelper->shouldReceive('addLowerTextOverlay')
            ->withArgs([Storage::disk('tmp')->path('tmp_addUpperTextOverlay'), self::TEST_DEALER_PHONE_NUMBER, $overlayParams])
            ->passthru();

        $this->imageHelper->shouldNotReceive('addLogoOverlay');

        // Mock Temp Filenames
        $this->imageHelper
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('getRandomString')
            ->andReturn(
                'tmp_addUpperTextOverlay',
                'tmp_addLowerTextOverlay',
            );

        $imageService = app()->make(ImageService::class);

        $newImage = $imageService->addOverlays($imagePath, $overlayParams);

        Storage::disk('tmp')->assertExists([
            'tmp_addLowerTextOverlay'
        ]);

        Storage::disk('tmp')->assertMissing([
            'tmp_addUpperTextOverlay',
        ]);

        $this->assertEquals(mime_content_type($newImage), 'image/png');

        // to compare the image manually by human
        // Storage::disk('test_resources')->put('testAddLowerTextUpperTextOverlays.png', 
        //     file_get_contents($newImage));

        $this->assertImages($newImage,
            Storage::disk('test_resources')->path('testAddLowerTextUpperTextOverlays.png'));
    }

    /**
     * @dataProvider overlayParamDataProvider
     * @group overlay_images
     */
    public function testAddLogoOnlyOverlay($overlayParams)
    {
        $overlayParams['overlay_logo'] = Storage::disk('test_resources')->path('logo_image.png');
        $overlayParams['overlay_upper'] = User::OVERLAY_UPPER_NONE;

        $imagePath = Storage::disk('test_resources')->path('inventory_image.png');

        $this->imageHelper->shouldReceive('addLogoOverlay')
            ->withArgs([
                $imagePath,
                Storage::disk('test_resources')->path('logo_image.png'),
                $overlayParams
            ])
            ->passthru();

        $this->imageHelper->shouldNotReceive('addUpperTextOverlay');
        $this->imageHelper->shouldNotReceive('addLowerTextOverlay');

        // Mock Temp Filenames
        $this->imageHelper
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('getRandomString')
            ->andReturn(
                'tmp_localLogoPath',
                'tmp_resizedLogo',
                'tmp_localImagePath',
                'tmp_addLogoOverlay',
            );

        $imageService = app()->make(ImageService::class);

        $newImage = $imageService->addOverlays($imagePath, $overlayParams);

        Storage::disk('tmp')->assertExists([
            'tmp_addLogoOverlay'
        ]);

        Storage::disk('tmp')->assertMissing([
            'tmp_localLogoPath',
            'tmp_resizedLogo',
            'tmp_localImagePath',
        ]);

        $this->assertEquals(mime_content_type($newImage), 'image/png');

        // to compare the image manually by human
        // Storage::disk('test_resources')->put('testAddLogoOnlyOverlay.png', 
        //     file_get_contents($newImage));

        $this->assertImages($newImage,
            Storage::disk('test_resources')->path('testAddLogoOnlyOverlay.png'));
    }

    private function assertImages($expectedPath, $outputPath)
    {
        $expectedImage = new Imagick($expectedPath);
        $expectedSignature = $expectedImage->getImageSignature();

        $outputImage = new Imagick($outputPath);
        $outputSignature = $outputImage->getImageSignature();

        $this->assertEquals($expectedSignature, $outputSignature);
    }
}
