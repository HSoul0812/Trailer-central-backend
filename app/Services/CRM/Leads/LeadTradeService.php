<?php

namespace App\Services\CRM\Leads;

use App\Models\CRM\Leads\LeadTrade;
use App\Models\CRM\Leads\LeadTradeImage;
use App\Exceptions\File\FileUploadException;
use App\Exceptions\File\MissingS3FileException;
use Illuminate\Support\Facades\Storage;
use App\Repositories\CRM\Leads\LeadTradeRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Http\UploadedFile;
use App\Helpers\ImageHelper;

class LeadTradeService implements LeadTradeServiceInterface {

    /**
     * @var LeadTradeRepositoryInterface
     */
    private $leadTradeRepository;
    /**
     * @var ImageHelper
     */
    private $imageHelper;

    public function __construct(LeadTradeRepositoryInterface $leadTradeRepository, ImageHelper $imageHelper)
    {
        $this->leadTradeRepository = $leadTradeRepository;
        $this->imageHelper = $imageHelper;
    }

    /**
     * @param array $params
     * @return LeadTrade
     */
    public function create(array $params)
    {
        $trade = $this->leadTradeRepository->create($params);

        if (isset($params['images']) && is_array($params['images'])) {

            foreach ($params['images'] as $file) {

                if (!($file instanceof UploadedFile)) continue;
            
                // upload file
                $fileContent = $file->get();
                $fileName = $file->getClientOriginalName();
                $randomS3Filename = $this->imageHelper->getRandomString($fileContent);
                Storage::disk('s3')->put($randomS3Filename, $fileContent);

                $this->leadTradeRepository->createImage([
                    'trade_id' => $trade->id,
                    'filename' => $fileName,
                    'path' => Storage::disk('s3')->url($randomS3Filename)
                ]);
            }
        }

        return $trade;
    }

    /**
     * @param array $params
     * @return LeadTrade
     */
    public function update(array $params): LeadTrade
    {
        $trade = $this->leadTradeRepository->update($params);
        $existingImageIds = $this->leadTradeRepository->getImageIds($params['id']);

        if (isset($params['new_images']) && is_array($params['new_images'])) {

            foreach ($params['new_images'] as $file) {

                if (!($file instanceof UploadedFile)) continue;
            
                // upload file
                $fileContent = $file->get();
                $fileName = $file->getClientOriginalName();
                $randomS3Filename = $this->imageHelper->getRandomString($fileContent);
                Storage::disk('s3')->put($randomS3Filename, $fileContent);

                $this->leadTradeRepository->createImage([
                    'trade_id' => $params['id'],
                    'filename' => $fileName,
                    'path' => Storage::disk('s3')->url($randomS3Filename)
                ]);
            }
        }

        $imageIdsToKeep = [];
        if (isset($params['existing_images']) && is_array($params['existing_images'])) {

            foreach ($params['existing_images'] as $imageData) {

                $imageIdsToKeep[] = $imageData['id'];
            }
        }

        $imageIdsToBeDeleted = array_diff($existingImageIds, $imageIdsToKeep);

        foreach ($imageIdsToBeDeleted as $deletingImageId) {
            $this->deleteImage($deletingImageId);
        }

        return $trade;
    }

    /**
     * @param array $params
     * @return bool
     */
    public function delete(array $params): bool
    {
        $imageIds = $this->leadTradeRepository->getImageIds($params['id']);

        $deleted = $this->leadTradeRepository->delete($params);

        foreach ($imageIds as $imageId) {

            $this->deleteImage($imageId);
        }

        return $deleted;
    }

    /**
     * @param array $params
     * @return bool
     */
    public function deleteImage(int $imageId): bool
    {
        $fullImagepath = $this->leadTradeRepository->getImagePath($imageId);
        $s3Path = parse_url($fullImagepath, PHP_URL_PATH);
        
        $deleted = $this->leadTradeRepository->deleteImage($imageId);

        Storage::disk('s3')->delete($s3Path);

        return $deleted;
    }
}