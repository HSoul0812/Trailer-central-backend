<?php

namespace Tests\Unit\Services\CRM\Documents;

use Mockery;
use Mockery\LegacyMockInterface;
use Tests\TestCase;
use App\Repositories\CRM\Documents\DealerDocumentsRepositoryInterface;
use App\Helpers\ImageHelper;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Services\CRM\Documents\DealerDocumentsServiceInterface;
use App\Services\CRM\Documents\DealerDocumentsService;
use App\Models\CRM\Documents\DealerDocuments;

class DealerDocumentsServiceTest extends TestCase {

    const TEST_LEAD_ID = PHP_INT_MAX - 1;
    const TEST_DEALER_ID = PHP_INT_MAX - 2;
    const TEST_DOCUMENT_ID = PHP_INT_MAX - 3;

    /**
     * @var LegacyMockInterface|DealerDocumentsRepositoryInterface
     */
    protected $documentRepositoryMock;

    /**
     * @var LegacyMockInterface|ImageHelper
     */
    protected $imageHelper;

    public function setUp(): void
    {
        parent::setUp();

        $this->instanceMock('documentRepositoryMock', DealerDocumentsRepositoryInterface::class);

        $this->instanceMock('imageHelper', ImageHelper::class);
    }

    /**
     * @group CRM
     */
    public function testCreate()
    {
        Storage::fake('s3');

        $files = []; $uploadedFilePaths = [];
        for ($i = 0; $i < 5; $i++) {

            $filename = 'document'. ($i+1) .'.pdf';
            $files[] = UploadedFile::fake()->create($filename);

            $randomString = 'randomString'. ($i+1);
            $this->imageHelper
                ->shouldAllowMockingProtectedMethods()
                ->shouldReceive('getRandomString')
                ->once()->andReturn($randomString);
            $uploadedFilePaths[] = $randomString;

            $this->documentRepositoryMock->shouldReceive('create')
                ->once()
                ->with([
                    'lead_id' => self::TEST_LEAD_ID,
                    'dealer_id' => self::TEST_DEALER_ID,
                    'filename' => $filename,
                    'full_path' => Storage::disk('s3')->url($randomString)
                ])
                ->andReturn($this->getEloquentMock(DealerDocuments::class));
        }

        $service = $this->app->make(DealerDocumentsService::class);
        $service->create([
            'lead_id' => self::TEST_LEAD_ID,
            'dealer_id' => self::TEST_DEALER_ID,
            'files' => $files
        ]);

        Storage::disk('s3')->assertExists($uploadedFilePaths);

        Storage::fake('s3');
    }

    /**
     * @group CRM
     */
    public function testDelete()
    {
        Storage::fake('s3');

        $filePath = md5(time());
        $document = $this->getEloquentMock(DealerDocuments::class);
        $document->id = self::TEST_DOCUMENT_ID;
        $document->full_path = 'https://test.com/'. $filePath;

        Storage::disk('s3')->put($filePath, '');
        Storage::disk('s3')->assertExists($filePath);

        $this->documentRepositoryMock->shouldReceive('find')
            ->once()
            ->with(self::TEST_DOCUMENT_ID)
            ->andReturn($document);

        $this->documentRepositoryMock->shouldReceive('delete')
            ->once()
            ->with(['document_id' => self::TEST_DOCUMENT_ID])
            ->andReturn(true);

        $service = $this->app->make(DealerDocumentsService::class);
        $service->delete([
            'document_id' => self::TEST_DOCUMENT_ID
        ]);

        Storage::disk('s3')->assertMissing($filePath);

        Storage::fake('s3');
    }
}