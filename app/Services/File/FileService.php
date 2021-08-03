<?php

namespace App\Services\File;

use App\Exceptions\File\FileUploadException;
use App\Services\File\DTOs\FileDto;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Class FileService
 * @package App\Services\File
 */
class FileService extends AbstractFileService
{
    private const EXTENSION_MAPPING = [
        'application/pdf' => 'pdf',
    ];

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
     *
     * @throws FileUploadException
     * @throws FileNotFoundException
     */
    public function uploadLocal(array $data): FileDto
    {
        if (!isset($data['file']) || !$data['file'] instanceof UploadedFile) {
            throw new FileUploadException("file has been missed");
        }

        $file = $data['file'];

        if (!in_array($file->getMimeType(), array_keys(self::EXTENSION_MAPPING))) {
            throw new FileUploadException("Not expected mime type");
        }

        $content = $file->get();

        $params['dealer_id'] = $data['dealer_id'] ?? null;
        $params['extension'] = self::EXTENSION_MAPPING[$file->getMimeType()];

        $localDisk = Storage::disk('local_tmp');

        $localFilename = $this->uploadLocalByContent($content, $localDisk, $params);

        if ($localFilename) {
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($localFilename);
        }

        if (!isset($mimeType)) {
            throw new FileUploadException("Can't get content");
        }

        $url = $localDisk->url(str_replace($localDisk->path(''),'', $localFilename));

        return new FileDto($localFilename, null, $mimeType, $url);
    }
}
