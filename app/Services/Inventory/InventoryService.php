<?php

namespace App\Services\Inventory;

use App\Jobs\Files\DeleteFilesJob;
use App\Repositories\Inventory\FileRepositoryInterface;
use App\Repositories\Inventory\ImageRepositoryInterface;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Repositories\Repository;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Class InventoryService
 * @package App\Services\Inventory
 */
class InventoryService
{
    use DispatchesJobs;

    /**
     * @var InventoryRepositoryInterface
     */
    private $inventoryRepository;
    /**
     * @var ImageRepositoryInterface
     */
    private $imageRepository;
    /**
     * @var FileRepositoryInterface
     */
    private $fileRepository;

    /**
     * InventoryService constructor.
     * @param InventoryRepositoryInterface $inventoryRepository
     * @param ImageRepositoryInterface $imageRepository
     * @param FileRepositoryInterface $fileRepository
     */
    public function __construct(InventoryRepositoryInterface $inventoryRepository, ImageRepositoryInterface $imageRepository, FileRepositoryInterface $fileRepository)
    {
        $this->inventoryRepository = $inventoryRepository;
        $this->imageRepository = $imageRepository;
        $this->fileRepository = $fileRepository;
    }

    /**
     * @param int $inventoryId
     * @return bool
     */
    public function delete(int $inventoryId): bool
    {
        try {
            $imagesToDelete = new Collection;
            $filesToDelete = new Collection;

            $imageParams = [Repository::RELATION_WITH_COUNT => 'inventoryImages'];

            $images = $this->imageRepository->getAllByInventoryId($inventoryId, $imageParams);

            foreach ($images as $image) {
                // We remove images that are related only to the current item.
                if ($image->inventory_images_count === 1) {
                    $imagesToDelete->push($image);
                }
            }

            $fileParams = [Repository::RELATION_WITH_COUNT => 'inventoryFiles'];

            $files = $this->fileRepository->getAllByInventoryId($inventoryId, $fileParams);

            foreach ($files as $file) {
                // We remove files that are related only to the current item.
                if ($file->inventory_files_count === 1) {
                    $filesToDelete->push($file);
                }
            }

            if (!$imagesToDelete->isEmpty()) {
                $imagesFilenames = $imagesToDelete->pluck('filename')->toArray();
                $imagesIds = $imagesToDelete->pluck('image_id')->toArray();

                $deleteImagesParams = [
                    Repository::CONDITION_AND_WHERE_IN => [
                        'image_id' => $imagesIds
                    ]
                ];

                $this->imageRepository->delete($deleteImagesParams);

                $this->dispatch((new DeleteFilesJob($imagesFilenames))->onQueue('files'));
            }

            if (!$filesToDelete->isEmpty()) {
                $filesFilenames = $filesToDelete->pluck('path')->toArray();
                $filesIds = $filesToDelete->pluck('id')->toArray();

                $deleteFilesParams = [
                    Repository::CONDITION_AND_WHERE_IN => [
                        'id' => $filesIds
                    ]
                ];

                $this->fileRepository->delete($deleteFilesParams);

                $this->dispatch((new DeleteFilesJob($filesFilenames))->onQueue('files'));
            }

            $this->inventoryRepository->delete(['id' => $inventoryId]);

            Log::info('Item has been successfully deleted', ['inventoryId' => $inventoryId]);
        } catch (\Exception $e) {
            Log::error('Item delete error.', $e->getTrace());
            return false;
        }

        return true;
    }
}
