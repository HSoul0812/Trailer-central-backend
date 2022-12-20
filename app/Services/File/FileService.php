<?php

namespace App\Services\File;

use App\Exceptions\File\FileUploadException;
use App\Services\File\DTOs\FileDto;
use App\Services\Integration\Common\DTOs\AttachmentFile;
use App\Traits\S3\S3Helper;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

/**
 * Class FileService
 * @package App\Services\File
 */
class FileService extends AbstractFileService
{
    use S3Helper;

    private const EXTENSION_MAPPING = [
        'image/jpeg' => 'jpeg',
        'image/jpg' => 'jpg',
        'image/gif' => 'gif',
        'image/png' => 'png',
        'image/bmp' => 'bmp',
        'image/tiff' => 'tiff',

        'audio/mp4' => 'mp4',
        'audio/ogg' => 'oga',
        'audio/mpeg' => 'mp3',
        'audio/3gpp' => '3gp',
        'audio/3gpp2' => '3g2',
        'audio/webm' => 'weba',

        'video/mpeg' => 'mpeg',
        'video/mp4' => 'mp4',
        'video/webm' => 'webm',
        'video/3gpp' => '3gp',
        'video/3gpp2' => '3g2',

        'text/csv' => 'csv',
        'text/plain' => 'csv',
        'text/calendar' => 'ics',
        'text/rtf' => 'rtf',

        'application/pdf' => 'pdf',
        'application/rtf' => 'rtf',
    ];

    /**
     * @param array $files
     * @param int|null $dealerId
     * @return Collection|null
     * @throws FileUploadException
     */
    public function bulkUpload(array $files, ?int $dealerId = null): ?Collection
    {
        $result = new Collection();

        foreach ($files as $file) {
            $result->push($this->upload($file, null, $dealerId));
        }

        return $result;
    }

    /**
     * @param string $url
     * @param string|null $title
     * @param int|null $dealerId
     * @param int|null $identifier
     * @param array $params
     * @return array|null
     *
     * @throws FileUploadException
     */
    public function upload(string $url, ?string $title = null, ?int $dealerId = null, ?int $identifier = null, array $params = []): ?FileDto
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

        if (!$title) {
            $title = basename(parse_url($url, PHP_URL_PATH));
        }

        $s3Filename = $this->sanitizeHelper->cleanFilename($title);

        $s3Path = $this->uploadToS3($localFilename, $s3Filename, $dealerId, $identifier, ['mimetype' => $mimeType]);

        return new FileDto($s3Path, null, $mimeType, $this->getS3Url($s3Path));
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
        if (!isset($data['file']) || (!$data['file'] instanceof UploadedFile && !$data['file'] instanceof AttachmentFile)) {
            throw new FileUploadException("file has been missed");
        }

        $file = $data['file'];

        if (!in_array($file->getMimeType(), array_keys(self::EXTENSION_MAPPING))) {
            throw new FileUploadException("Not expected mime type");
        }

        $content = $file instanceof UploadedFile ? $file->get() : $file->getContents();

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
