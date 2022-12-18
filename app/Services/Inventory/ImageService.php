<?php

namespace App\Services\Inventory;

use App\Repositories\Inventory\ImageRepositoryInterface;
use App\Exceptions\File\MissingS3FileException;
use Illuminate\Support\Facades\Storage;
use App\Helpers\ImageHelper;
use App\Traits\S3\S3Helper;
use App\Models\Inventory\Image;

class ImageService implements ImageServiceInterface 
{
    use S3Helper;
    /**
     * @var ImageRepositoryInterface
     */
    private $imageRepository;

    /**
     * @param ImageRepositoryInterface $imageRepository
     */
    public function __construct(
        ImageRepositoryInterface $imageRepository
    ) {
        $this->imageRepository = $imageRepository;
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
    protected function getFileHash(string $filename)
    {
        if (Storage::disk('s3')->missing($filename))
            throw new MissingS3FileException;

        return sha1_file($this->getS3BaseUrl() . $filename);
    }
}