<?php

namespace App\Services\Integrations\TrailerCentral\Api\Image;

use App\Domains\Images\Actions\DeleteOldLocalImagesAction;
use App\Repositories\Integrations\TrailerCentral\AuthTokenRepositoryInterface;
use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Utils;
use Illuminate\Http\UploadedFile;
use Log;
use Storage;

class ImageService implements ImageServiceInterface
{
    const LOCAL_IMAGES_DISK = 'local_tmp';

    const LOCAL_IMAGES_DIRECTORY = 'images';

    private string $endpointUrl;

    public function __construct(
        private GuzzleHttpClient $httpClient,
        private AuthTokenRepositoryInterface $authTokenRepository,
        private DeleteOldLocalImagesAction $deleteOldLocalImagesAction,
    ) {
        $this->endpointUrl = config('services.trailercentral.api') . 'images/local';
    }

    /**
     * @throws GuzzleException
     */
    public function uploadImage(int $dealerId, string $imagePath)
    {
        $tcAuthToken = $this->authTokenRepository->get(['user_id' => $dealerId]);
        try {
            $response = $this->httpClient->post($this->endpointUrl, [
                'headers' => [
                    'access-token' => $tcAuthToken->access_token
                ],
                'multipart' => [
                    [
                        'name' => 'dealer_id',
                        'contents' => $dealerId
                    ],
                    [
                        'name' => 'file',
                        'contents' => Utils::tryFopen($imagePath, 'r')
                    ]
                ]
            ]);
            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::info('Exception was thrown while calling TrailerCentral API.');
            Log::info($e->getCode() . ': ' . $e->getMessage());

            throw $e;
        }

    }

    public function uploadLocalImage(UploadedFile $uploadedFile): string
    {
        $storage = Storage::disk(self::LOCAL_IMAGES_DISK);

        $fileName = $uploadedFile->hashName();

        $storage->putFileAs(self::LOCAL_IMAGES_DIRECTORY, $uploadedFile, $fileName);

        return $storage->url(sprintf("%s/$fileName", self::LOCAL_IMAGES_DIRECTORY));
    }

    public function deleteOldLocalImages(int $olderThanDays): void
    {
        $this->deleteOldLocalImagesAction->execute($olderThanDays);
    }
}
