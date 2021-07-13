<?php

namespace App\Services\File;

use App\Exceptions\File\FileUploadException;
use App\Services\File\DTOs\FileDto;

/**
 * Class FileService
 * @package App\Services\File
 */
class FileService extends AbstractFileService
{
    /**
     * @param string $url
     * @param string $title
     * @param int|null $dealerId
     * @param int|null $identifier
     * @param array $params
     * @return array|null
     *
     * @throws FileUploadException
     */
    public function upload(string $url, string $title, ?int $dealerId = null, ?int $identifier = null, array $params = []): ?FileDto
    {
        $skipNotExisting = $params['skipNotExisting'] ?? false;

        $localFilename = $this->uploadLocalByUrl($url, $dealerId, $identifier, $skipNotExisting);

        if ($localFilename) {
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($localFilename);
        }

        if (!$skipNotExisting && !isset($mimeType)) {
            throw new FileUploadException("Can't get content. Url - {$url}");
        }

        if ($skipNotExisting && !isset($mimeType)) {
            return null;
        }

        $s3Filename = $this->sanitizeHelper->cleanFilename($title);

        $s3Path = $this->uploadToS3($localFilename, $s3Filename, $dealerId, $identifier, ['mimetype' => $mimeType]);

        return new FileDto($s3Path, null, $mimeType);
    }

    /**
     * @param array $data
     * @return FileDto
     * @throws FileUploadException
     */
    public function uploadLocal(array $data): FileDto
    {
        if (!isset($data['file'])) {
            throw new FileUploadException("file has been missed");
        }

        $localFilename = $this->uploadLocalByContent($data['file'], $data['dealer_id'] ?? null);

        if ($localFilename) {
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($localFilename);
        }

        if (!isset($mimeType)) {
            throw new FileUploadException("Can't get content");
        }

        return new FileDto($localFilename, null, $mimeType);
    }
}
