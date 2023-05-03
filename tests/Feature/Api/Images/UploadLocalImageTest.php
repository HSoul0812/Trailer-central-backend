<?php

namespace Tests\Feature\Api\Images;

use App\Services\Integrations\TrailerCentral\Api\Image\ImageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Storage;
use Symfony\Component\HttpFoundation\Response;
use Tests\Common\FeatureTestCase;

class UploadLocalImageTest extends FeatureTestCase
{
    public const UPLOAD_LOCAL_IMAGE_ENDPOINT = '/api/images/local';

    public function testItCanThrowValidationErrorWhenFileIsNotImage()
    {
        $this
            ->withToken($this->jwtAuthToken())
            ->post(self::UPLOAD_LOCAL_IMAGE_ENDPOINT, [
               'file' => UploadedFile::fake()->createWithContent('text-file.txt', 'empty'),
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertSee('The file must be an image.');
    }

    public function testItCanUploadTheLocalImage()
    {
        $storage = Storage::disk(ImageService::LOCAL_IMAGES_DISK);

        $response = $this
            ->withToken($this->jwtAuthToken())
            ->post(self::UPLOAD_LOCAL_IMAGE_ENDPOINT, [
                'file' => UploadedFile::fake()->image('image1.jpg'),
            ])
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'url',
                ],
            ]);

        $imagePath = (string) Str::of($response->json('data.url'))->remove(config('app.url') . '/upload/tmp/');

        $storage->assertExists($imagePath);

        $storage->delete($imagePath);
    }
}
