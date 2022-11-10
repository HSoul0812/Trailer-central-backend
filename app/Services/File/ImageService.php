<?php

namespace App\Services\File;

use App\Exceptions\File\FileUploadException;
use App\Exceptions\File\ImageUploadException;
use App\Helpers\ImageHelper;
use App\Helpers\SanitizeHelper;
use App\Services\File\DTOs\FileDto;
use App\Traits\CompactHelper;
use GuzzleHttp\Client;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Class ImageService
 * @package App\Services\File
 */
class ImageService extends AbstractFileService
{
    /**
     * @var ImageHelper
     */
    private $imageHelper;

    private const EXTENSION_MAPPING = [
        "image/gif" => "gif",
        "image/jpeg" => "jpg",
        "image/png" => "png",
    ];

    public function __construct(Client $httpClient, SanitizeHelper $sanitizeHelper, ImageHelper $imageHelper)
    {
        parent::__construct($httpClient, $sanitizeHelper);

        $this->imageHelper = $imageHelper;
    }

    /**
     * @param string $url
     * @param string $title
     * @param int|null $dealerId
     * @param int|null $identifier
     * @param array $params
     * @return FileDto|null
     *
     * @throws ImageUploadException
     * @throws FileUploadException
     */
    public function upload(string $url, string $title, ?int $dealerId = null, ?int $identifier = null, array $params = []): ?FileDto
    {
        $skipNotExisting = $params['skipNotExisting'] ?? false;
        $overlayText = $params['overlayText'] ?? null;

        $localFilename = $this->uploadLocalByUrl($url, $dealerId, $identifier, $skipNotExisting);

        if ($localFilename) {
            $imageInfo = getimagesize($localFilename);
        }

        if (!$skipNotExisting && (!isset($imageInfo['mime']) || !in_array($imageInfo['mime'], array_keys(self::EXTENSION_MAPPING)))) {
            throw new ImageUploadException("Not expected mime-type. Url - {$url}");
        }

        if ($skipNotExisting && (!isset($imageInfo['mime']) || !in_array($imageInfo['mime'], array_keys(self::EXTENSION_MAPPING)))) {
            return null;
        }

        $extension = self::EXTENSION_MAPPING[$imageInfo['mime']];

        $inventoryFilenameTitle = $title . "_" . CompactHelper::getRandomString() . ($overlayText ? ("_overlay_" . time()) : '') . ".{$extension}";
        $s3Filename = $this->sanitizeHelper->cleanFilename($inventoryFilenameTitle);

        $this->imageHelper->resize($localFilename, 800, 800, true);

        if ($overlayText) {
            $this->imageHelper->addOverlay($localFilename, $overlayText);
        }

        $s3Path = $this->uploadToS3($localFilename, $s3Filename, $dealerId, $identifier, $params);

        $hash = sha1_file($localFilename);
        unlink($localFilename);

        return new FileDto($s3Path, $hash);
    }

    /**
     * @param array $data
     * @return FileDto
     *
     * @throws ImageUploadException
     * @throws FileUploadException
     * @throws FileNotFoundException
     */
    public function uploadLocal(array $data): FileDto
    {
        if (!isset($data['file']) || !$data['file'] instanceof UploadedFile) {
            throw new ImageUploadException("file has been missed");
        }

        $file = $data['file'];

        if (!in_array($file->getMimeType(), array_keys(self::EXTENSION_MAPPING))) {
            throw new ImageUploadException("Not expected mime type");
        }

        $content = $file->get();

        $params['dealer_id'] = $data['dealer_id'] ?? null;
        $params['extension'] = self::EXTENSION_MAPPING[$file->getMimeType()];

        $localDisk = Storage::disk('local_tmp');

        $localFilename = $this->uploadLocalByContent($content, $localDisk, $params);

        $hash = sha1_file($localFilename);

        $url = $localDisk->url(str_replace($localDisk->path(''),'', $localFilename));

        return new FileDto($localFilename, $hash, null, $url);
    }
}
