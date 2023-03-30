<?php

namespace App\Services\Inventory;

use App\Jobs\Inventory\GenerateOverlayImageJobByDealer;
use App\Repositories\Inventory\ImageRepositoryInterface;
use App\Exceptions\File\MissingS3FileException;
use Illuminate\Support\Facades\Storage;
use App\Traits\S3\S3Helper;
use App\Models\Inventory\Image;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Repositories\User\UserRepositoryInterface;
use App\Models\User\User;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Models\Inventory\Inventory;

class ImageService implements ImageServiceInterface
{
    use S3Helper, DispatchesJobs;

    /** @var ImageRepositoryInterface */
    private $imageRepository;

    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var InventoryRepositoryInterface */
    private $inventoryRepository;

    public function __construct(
        ImageRepositoryInterface $imageRepository,
        UserRepositoryInterface $userRepository,
        InventoryRepositoryInterface $inventoryRepository
    ) {
        $this->imageRepository = $imageRepository;
        $this->userRepository = $userRepository;
        $this->inventoryRepository = $inventoryRepository;
    }

    /**
     * @throws MissingS3FileException
     */
    public function saveOverlay(Image $image, string $filename): void
    {
        if (Storage::disk('s3')->missing($filename)) {
            throw new MissingS3FileException(sprintf("S3 object '%s' is missing", $filename));
        }

        $objectUrlToBeDropped = $image->filename;

        $this->imageRepository->update([
            'id' => $image->image_id,
            'hash' =>  $this->getFileHash($filename),
            'filename' => $filename,
            'filename_with_overlay' => $filename
        ]);

        $this->inventoryRepository->markImageAsOverlayGenerated($image->image_id);
        $this->imageRepository->scheduleObjectToBeDroppedByURL($objectUrlToBeDropped);
    }

    /**
     * Will do nothing when image `filename_with_overlay` is empty which means it has never had an overlay
     *
     * @throws MissingS3FileException
     */
    public function tryToRestoreOriginalImage(Image $image): void
    {
        if (empty($image->filename_with_overlay)) {
            return;
        }

        // swap the overlay filename to the filename without overlay
        $this->imageRepository->update([
            'id' => $image->image_id,
            // @todo investigate what the purpose of `hash` value
            'hash' => $this->getFileHash($image->filename_without_overlay),
            'filename' => $image->filename_without_overlay
        ]);
    }

    /**
     * Will do nothing when image `filename_with_overlay` is empty which means it has never had an overlay
     *
     * @throws MissingS3FileException
     */
    public function tryToRestoreImageOverlay(Image $image): void
    {
        if (empty($image->filename_with_overlay)) {
            return;
        }

        // swap the overlay filename to the filename without overlay
        $this->imageRepository->update([
            'id' => $image->image_id,
            // @todo investigate what the purpose of `hash` value
            'hash' => $this->getFileHash($image->filename_with_overlay),
            'filename' => $image->filename_with_overlay
        ]);
    }

    /**
     * @throws MissingS3FileException
     */
    public function getFileHash(string $filename): string
    {
        if (Storage::disk('s3')->missing($filename)) {
            throw new MissingS3FileException;
        }

        return sha1_file($this->getS3BaseUrl() . $filename);
    }

    /**
     *  Will update overlay settings only when they were really changed
     *
     * @param  array{
     *     dealer_id:int,
     *     inventory_id: int,
     *     overlay_logo: string,
     *     overlay_logo_position: string,
     *     overlay_logo_width: int,
     *     overlay_upper: string,
     *     overlay_upper_bg: string,
     *     overlay_upper_alpha: string,
     *     overlay_upper_text: string,
     *     overlay_upper_size: int,
     *     overlay_upper_margin: string,
     *     overlay_lower: string,
     *     overlay_lower_bg: string,
     *     overlay_lower_alpha: string,
     *     overlay_lower_text: string,
     *     overlay_lower_size: int,
     *     overlay_lower_margin: string,
     *     overlay_default: int,
     *     overlay_enabled: int,
     *     dealer_overlay_enabled: int,
     *     overlay_text_dealer: string,
     *     overlay_text_phone: string,
     *     country: string,
     *     overlay_text_location: string,
     *     overlay_updated_at: string
     *     }  $params
     * @return User
     */
    public function updateOverlaySettings(array $params): User
    {
        $changes = $this->userRepository->updateOverlaySettings($params['dealer_id'], $params);
        $dealer = $this->userRepository->get(['dealer_id' => $params['dealer_id']]);
        $wasChanged = !empty($changes);
        $isOverlayEnabledChanged = isset($changes['overlay_enabled']);

        // update overlay_enabled on all inventories
        if ($isOverlayEnabledChanged) {
            Inventory::withoutCacheInvalidationAndSearchSyncing(function () use ($params, $changes) {
                $this->inventoryRepository->massUpdate([
                    'dealer_id' => $params['dealer_id'],
                    'overlay_enabled' => $changes['overlay_enabled']
                ]);
            });
        }

        // Generate Overlay Inventory Images if necessary
        if ($wasChanged) {
            // @todo we should implement some mechanism to avoid to dispatch many times
            //      `GenerateOverlayImageJobByDealer` successively because that job will spawn as many
            //      `GenerateOverlayImageJob` jobs as many inventory units has the dealer
            $this->dispatch((new GenerateOverlayImageJobByDealer($dealer->dealer_id))->delay(2));
        }

        return $dealer;
    }
}
