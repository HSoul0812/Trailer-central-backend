<?php

namespace Tests\Integration\Http\Controllers\CRM\Email;

use Tests\Integration\IntegrationTestCase;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User\CrmUser;
use App\Models\User\NewUser;
use App\Models\User\NewDealerUser;
use App\Models\User\AuthToken;
use App\Models\User\User;
use Illuminate\Http\UploadedFile;
use App\Services\CRM\Email\MosaicoServiceInterface;
use App\Repositories\User\NewDealerUserRepositoryInterface;
use App\Repositories\CRM\User\CrmUserRepositoryInterface;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Imagick;
use Illuminate\Support\Facades\Mail;
use App\Mail\CRM\Interactions\EmailBuilderEmail;
use App\Services\CRM\Email\EmailBuilderServiceInterface;

/**
 * Class MosaicoControllerTest
 * @package Tests\Integration\Http\Controllers\CRM\Email
 *
 * @coversDefaultClass \App\Http\Controllers\v1\CRM\Email\MosaicoController
 */
class MosaicoControllerTest extends IntegrationTestCase
{
    use DatabaseTransactions, WithFaker;

    const API_ENDPOINT = '/api/user/emailbuilder/mosaico';

    public function setUp(): void
    {
        parent::setUp();

        $this->dealer = factory(User::class)->create([
            'type' => User::TYPE_DEALER,
            'state' => User::STATUS_ACTIVE
        ]);

        $this->token = $this->dealer->authToken->access_token;

        /**
         * necessary data for CRM user
         */
        $user = factory(NewUser::class)->create();
        $newDealerUserRepo = app(NewDealerUserRepositoryInterface::class);
        $newDealerUser = $newDealerUserRepo->create([
            'user_id' => $user->user_id,
            'salt' => md5((string)$user->user_id), // random string
            'auto_import_hide' => 0,
            'auto_msrp' => 0
        ]);
        $this->dealer->newDealerUser()->save($newDealerUser);
        $crmUserRepo = app(CrmUserRepositoryInterface::class);
        $crmUserRepo->create([
            'user_id' => $user->user_id,
            'logo' => '',
            'first_name' => '',
            'last_name' => '',
            'display_name' => '',
            'dealer_name' => $this->dealer->name,
            'active' => 1
        ]);
        // END

        $this->instanceMock('emailServiceMock', EmailBuilderServiceInterface::class);
    }

    public function tearDown(): void
    {
        $userId = $this->dealer->newDealerUser->user_id;

        // Delete CRM User Related Data
        NewDealerUser::where(['user_id' => $userId])->delete();
        CrmUser::where(['user_id' => $userId])->delete();
        NewUser::destroy($userId);

        $this->dealer->authToken->delete();

        $this->dealer->delete();


        parent::tearDown();
    }

    /**
     * @group CRM
     * 
     * Test uploading and getting images
     */
    public function testImages()
    {
        $galleryFolder = str_replace('{dealerId}', $this->dealer->getKey(), MosaicoServiceInterface::IMAGE_GALLERY_FOLDER);
        $thumbnailFolder = str_replace('{dealerId}', $this->dealer->getKey(), MosaicoServiceInterface::IMAGE_THUMBNAIL_FOLDER);
        
        $response = $this->json(
            'POST',
            '/api/user/emailbuilder/mosaico/upload',
            [
                'files' => [
                    UploadedFile::fake()->image('image1.png', 1000),
                    UploadedFile::fake()->image('image2.png', 1000)
                ]
            ],
            ['access-token' => $this->token]
        );

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'name',
                        'url',
                        'size',
                        'thumbnailUrl'
                    ]
                ]
            ])
            ->assertJsonPath('data.0.name', 'image1.png')
            ->assertJsonPath('data.0.url', Storage::disk('s3')->url($galleryFolder .'/image1.png'))
            ->assertJsonPath('data.0.thumbnailUrl', Storage::disk('s3')->url($thumbnailFolder .'/image1.png'))
            ->assertJsonPath('data.1.name', 'image2.png')
            ->assertJsonPath('data.1.url', Storage::disk('s3')->url($galleryFolder .'/image2.png'))
            ->assertJsonPath('data.1.thumbnailUrl', Storage::disk('s3')->url($thumbnailFolder .'/image2.png'));

        Storage::disk('s3')->assertExists([
           $galleryFolder .'/image1.png',
           $galleryFolder .'/image2.png',
           $thumbnailFolder .'/image1.png',
           $thumbnailFolder .'/image2.png'
        ]);

        $response = $this->json(
            'GET',
            '/api/user/emailbuilder/mosaico/upload',
            [],
            ['access-token' => $this->token]
        );

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'name',
                        'url',
                        'size',
                        'thumbnailUrl'
                    ]
                ]
            ])
            ->assertJsonPath('data.0.name', 'image1.png')
            ->assertJsonPath('data.0.url', Storage::disk('s3')->url($galleryFolder .'/image1.png'))
            ->assertJsonPath('data.0.thumbnailUrl', Storage::disk('s3')->url($thumbnailFolder .'/image1.png'))
            ->assertJsonPath('data.1.name', 'image2.png')
            ->assertJsonPath('data.1.url', Storage::disk('s3')->url($galleryFolder .'/image2.png'))
            ->assertJsonPath('data.1.thumbnailUrl', Storage::disk('s3')->url($thumbnailFolder .'/image2.png'));

        // Clear Image after Test
        Storage::disk('s3')->delete([
            $galleryFolder .'/image1.png',
            $galleryFolder .'/image2.png',
            $thumbnailFolder .'/image1.png',
            $thumbnailFolder .'/image2.png'
         ]);
    }

    /**
     * @group CRM
     */
    public function testGetImagePlaceholder()
    {
        $response = $this->json(
            'GET',
            '/api/user/emailbuilder/mosaico/img?params=400,400&method=placeholder',
            [],
            ['access-token' => $this->token]
        );

        $response->assertStatus(200);
    }

    /**
     * @group CRM
     */
    public function testResizeImage()
    {
        // Upload a Test Image
        Storage::disk('s3')->put('test/inventory_image.png',
            file_get_contents(Storage::disk('test_resources')->path('inventory_image.png')));

        $response = $this->json(
            'GET',
            '/api/user/emailbuilder/mosaico/img?src='. urlencode(Storage::disk('s3')->url('test/inventory_image.png')) 
                .'&method=resize&params=400,400',
            [],
            ['access-token' => $this->token]
        );

        $response->assertStatus(200);

        // Delete Image after Testing
        Storage::disk('s3')->delete('test/inventory_image.png');
    }

    /**
     * @group CRM
     */
    public function testCoverImage()
    {
        // Upload a Test Image
        Storage::disk('s3')->put('test/inventory_image.png',
            file_get_contents(Storage::disk('test_resources')->path('inventory_image.png')));

        $response = $this->json(
            'GET',
            '/api/user/emailbuilder/mosaico/img?src='. urlencode(Storage::disk('s3')->url('test/inventory_image.png')) 
                .'&method=cover&params=400,400',
            [],
            ['access-token' => $this->token]
        );

        $response->assertStatus(200);

        // Delete Image after Testing
        Storage::disk('s3')->delete('test/inventory_image.png');

    }

    /**
     * @group CRM
     */
    public function testDownloadHtml()
    {
        $response = $this->json(
            'PUT',
            '/api/user/emailbuilder/mosaico',
            [
                'action' => 'download',
                'html' => $this->faker->randomHtml(2,3),
                'filename' => 'test.html'
            ],
            ['access-token' => $this->token]
        );

        $response->assertStatus(200);
        $response->assertHeader('content-disposition', 'attachment; filename=test.html');
    }

    /**
     * @group CRM
     */
    public function testEmailHtml()
    {
        $html = $this->faker->randomHtml(2,3);
        $emailSubject = 'test_subject';
        $emailAddress = $this->faker->email();

        // Mail::fake();
        $this->emailServiceMock->shouldReceive('testTemplate')
            ->once()
            ->with(
                $this->dealer->getKey(),
                $this->dealer->newDealerUser->user_id,
                $emailSubject,
                $html,
                $emailAddress
            );

        $response = $this->json(
            'PUT',
            '/api/user/emailbuilder/mosaico',
            [
                'action' => 'email',
                'html' => $html,
                'subject' => $emailSubject,
                'rcpt' => $emailAddress
            ],
            ['access-token' => $this->token]
        );

        $response->assertStatus(200);

        // Mail::assertSent(EmailBuilderEmail::class);
    }

    /**
     * @group CRM
     */
    public function testGetConfigs()
    {
        $galleryFolder = str_replace('{dealerId}', $this->dealer->getKey(), MosaicoServiceInterface::IMAGE_GALLERY_FOLDER);
        $thumbnailFolder = str_replace('{dealerId}', $this->dealer->getKey(), MosaicoServiceInterface::IMAGE_THUMBNAIL_FOLDER);
        $staticFolder = str_replace('{dealerId}', $this->dealer->getKey(), MosaicoServiceInterface::IMAGE_STATIC_FOLDER);

        $response = $this->json(
            'GET',
            '/api/user/emailbuilder/mosaico',
            [],
            ['access-token' => $this->token]
        );

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'THUMBNAIL_WIDTH' => MosaicoServiceInterface::THUMBNAIL_WIDTH,
                    'THUMBNAIL_HEIGHT' => MosaicoServiceInterface::THUMBNAIL_HEIGHT,
                    'IMAGE_GALLERY_FOLDER' => $galleryFolder,
                    'IMAGE_THUMBNAIL_FOLDER' => $thumbnailFolder,
                    'IMAGE_STATIC_FOLDER' => $staticFolder
                ]
            ]);
    }
}