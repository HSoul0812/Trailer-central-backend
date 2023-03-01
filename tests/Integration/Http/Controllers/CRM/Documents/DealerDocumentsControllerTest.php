<?php

namespace Tests\Integration\Http\Controllers\CRM\Documents;

use Tests\database\seeds\CRM\Documents\DealerDocumentsSeeder;
use Tests\Integration\IntegrationTestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\CRM\Documents\DealerDocuments;
use Mockery;
use Mockery\LegacyMockInterface;
use App\Helpers\ImageHelper;

/**
 * Class DealerDocumentsControllerTest
 * @package Tests\Integration\Http\Controllers\CRM\Documents
 *
 * @coversDefaultClass \App\Http\Controllers\v1\CRM\Documents\DealerDocumentsController
 */
class DealerDocumentsControllerTest extends IntegrationTestCase
{
    const API_URL = '/api/leads/{leadId}/documents';

    /**
     * @var LegacyMockInterface|ImageHelper
     */
    protected $imageHelper;

    public function setUp(): void
    {
        parent::setUp();

        $this->instanceMock('imageHelper', ImageHelper::class);
        
    }

    /**
     * @covers ::index
     * @group CRM
     */
    public function testIndex()
    {
        $documentsSeeder = new DealerDocumentsSeeder();
        $documentsSeeder->seed();

        $response = $this->json(
            'GET',
            str_replace('{leadId}', $documentsSeeder->lead->getKey(), self::API_URL),
            [],
            ['access-token' => $documentsSeeder->authToken->access_token]
        );

        $documents = $documentsSeeder->documents;

        $this->assertResponseDataEquals($response, $documents, false);

        $documentsSeeder->cleanUp();
    }

    /**
     * @covers ::index
     * @group CRM
     */
    public function testIndexWithWrongAccessToken()
    {
        $documentsSeeder = new DealerDocumentsSeeder();
        $documentsSeeder->seed();

        $response = $this->json(
            'GET',
            str_replace('{leadId}', $documentsSeeder->lead->getKey(), self::API_URL),
            [],
            ['access-token' => 'wrong_access_token']
        );

        $response
            ->assertStatus(403)
            ->assertSee('Invalid access token.');

        $documentsSeeder->cleanUp();
    }

    /**
     * @covers ::create
     * @group CRM
     */
    public function testCreate()
    {
        $documentsSeeder = new DealerDocumentsSeeder();
        $documentsSeeder->seed();

        $kiloBytes = 200;
        $fakeDocument1 = UploadedFile::fake()->create('document.pdf', $kiloBytes);
        $randomString = md5(time());

        $this->imageHelper
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('getRandomString')
            ->andReturn($randomString);

        $response = $this->json(
            'PUT',
            str_replace('{leadId}', $documentsSeeder->lead->getKey(), self::API_URL),
            [
                'files' => [
                    $fakeDocument1
                ]
            ],
            ['access-token' => $documentsSeeder->authToken->access_token]
        );

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'lead_id',
                        'dealer_id',
                        'filename',
                        'full_path'
                    ]
                ]
            ]);

        $this->assertDatabaseHas(DealerDocuments::getTableName(), [
            'lead_id' => $documentsSeeder->lead->getKey(),
            'dealer_id' => $documentsSeeder->dealer->getKey(),
            'filename' => 'document.pdf',
            'full_path' => Storage::disk('s3')->url($randomString)
        ]);
        Storage::disk('s3')->assertExists($randomString);

        // clear data
        $documentsSeeder->cleanUp();
        Storage::disk('s3')->delete($randomString);
    }

    /**
     * @covers ::destroy
     * @group CRM
     */
    public function testDestroy()
    {
        $documentsSeeder = new DealerDocumentsSeeder();
        $documentsSeeder->seed();

        // prepare test data
        // upload file
        $randomString = md5(time());
        Storage::disk('s3')->put($randomString, '');
        $uploadedFile = Storage::disk('s3')->url($randomString);

        // update document data with uploaded file
        $docId = $documentsSeeder->documents[0]['id'];
        DealerDocuments::find($docId)->update([
            'filename' => 'document.pdf',
            'full_path' => $uploadedFile
        ]);
        // end prepate test data

        // confirm test data
        $this->assertDatabaseHas(DealerDocuments::getTableName(), [
            'lead_id' => $documentsSeeder->lead->getKey(),
            'dealer_id' => $documentsSeeder->dealer->getKey(),
            'filename' => 'document.pdf',
            'full_path' => Storage::disk('s3')->url($randomString)
        ]);
        Storage::disk('s3')->assertExists($randomString);
        // end confirm test data

        $apiUrl = str_replace('{leadId}', $documentsSeeder->lead->getKey(), self::API_URL);
        $deleteApiUrl = $apiUrl .'/'. $docId;
        $response = $this->json(
            'DELETE',
            $deleteApiUrl,
            [],
            ['access-token' => $documentsSeeder->authToken->access_token]
        );

        $response->assertStatus(200);
    
        $this->assertDatabaseMissing(DealerDocuments::getTableName(), [
            'id' => $docId
        ]);
        Storage::disk('s3')->assertMissing($randomString);

        $documentsSeeder->cleanUp();
    }
}
