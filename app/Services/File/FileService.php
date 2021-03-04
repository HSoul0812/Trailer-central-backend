<?php

namespace App\Services\File;

use App\Exceptions\File\FileUploadException;

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
     * @throws \App\Exceptions\File\FileUploadException
     */
    public function upload(string $url, string $title, ?int $dealerId = null, ?int $identifier = null, array $params = []): ?array
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

        return [
            'path' => $s3Path,
            'type' => $mimeType
        ];
    }
}
