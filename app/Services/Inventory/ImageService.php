<?php

namespace App\Services\Inventory;

use App\Repositories\Inventory\ImageRepositoryInterface;
use App\Exceptions\File\MissingS3FileException;
use Illuminate\Support\Facades\Storage;
use App\Traits\S3\S3Helper;
use App\Models\Inventory\Image;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Jobs\Inventory\GenerateOverlayImageJob;
use App\Repositories\User\UserRepositoryInterface;
use App\Models\User\User;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Models\Inventory\Inventory;

class ImageService implements ImageServiceInterface
{
    use S3Helper, DispatchesJobs;

    /**
     * @var ImageRepositoryInterface
     */
    private $imageRepository;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var InventoryRepositoryInterface
     */
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
     * @param Image $image
     * @param string $filename
     * @return void
     */
    public function saveOverlay(Image $image, string $filename): void
    {
        if (Storage::disk('s3')->missing($filename))
            throw new MissingS3FileException;

        $params['filename'] = $filename;
        $params['filename_noverlay'] = $image->filename_noverlay;

        if (empty($params['filename_noverlay'])) {

            // keep original filename to other field
            $params['filename_noverlay'] = $image->filename;

        } else {

            // delete old s3 file
            Storage::disk('s3')->delete($image->filename);
        }

        $params['hash'] = $this->getFileHash($params['filename']);
        $params['id'] = $image->image_id;

        $this->imageRepository->update($params);
    }

    /**
     * @param Image $image
     * @param array $params
     * @return void
     */
    public function resetOverlay(Image $image): void
    {
        if (empty($image->filename_noverlay)) return;

        $params['filename'] = $image->filename_noverlay;
        $params['hash'] = $this->getFileHash($params['filename']);
        $params['filename_noverlay'] = '';
        $params['id'] = $image->image_id;

        // delete old s3 file
        Storage::disk('s3')->delete($image->filename);

        $this->imageRepository->update($params);
    }

    /**
     * Get Hash
     *
     * @param string $filename
     * @return string
     */
    public function getFileHash(string $filename): string
    {
        if (Storage::disk('s3')->missing($filename))
            throw new MissingS3FileException;

        return sha1_file($this->getS3BaseUrl() . $filename);
    }

    /**
     * Update Overlay Settings
     */
    public function updateOverlaySettings(array $params): User
    {
        $wasChanged = $this->userRepository->updateOverlaySettings($params['dealer_id'], $params);
        $dealer = $this->userRepository->get(['dealer_id' => $params['dealer_id']]);

        // Generate Overlay Inventory Images if necessary
        if ($wasChanged) {

            $inventories = $this->inventoryRepository->getAll(
                [
                    'dealer_id' => $params['dealer_id'],
                    'images_greater_than' => 1
                ], false, false, [Inventory::getTableName(). '.inventory_id']
            );

            if ($inventories->count() > 0) {
                foreach ($inventories as $inventory) {
                    $this->dispatch((new GenerateOverlayImageJob($inventory->inventory_id))->onQueue('overlay-images'));
                }

                // we can not inject `InventoryServiceInterface` into constructor to avoid cyclic dependency
                app(InventoryServiceInterface::class)->invalidateCacheAndReindexByDealerIds([$dealer->dealer_id]);
            }
        }

        return $dealer;
    }
}
