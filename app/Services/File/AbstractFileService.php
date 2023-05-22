<?php

namespace App\Services\File;

use App\Exceptions\File\FileUploadException;
use App\Exceptions\NotImplementedException;
use App\Helpers\SanitizeHelper;
use App\Traits\CompactHelper;
use GuzzleHttp\Client;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AbstractFileService
 * @package App\Services\File
 */
abstract class AbstractFileService implements FileServiceInterface
{
    const UPLOAD_TYPE_WEBSITE_MEDIA = 'website/media';
    const UPLOAD_TYPE_IMAGE = "media";
    const UPLOAD_TYPE_FILE = "media";
    const UPLOAD_TYPE_VIDEO = "media";
    const UPLOAD_TYPE_CSV = "uploads";
    const UPLOAD_TYPE_UNKNOWN = "uploads/abbandoned";

    private const LOCAL_FILENAME_FORMAT = '%s/%s.%s';
    private const LOCAL_FILENAME_DEFAULT_FORMAT = '%s/%s.tmp';

    private const RAND_MIN = 1000000000;
    private const RAND_MAX = 1000000000000;

    /**
     * @var Client
     */
    protected $httpClient;

    /**
     * @var SanitizeHelper
     */
    protected $sanitizeHelper;

    /**
     * ImageService constructor.
     * @param Client $httpClient
     * @param SanitizeHelper $sanitizeHelper
     */
    public function __construct(Client $httpClient, SanitizeHelper $sanitizeHelper)
    {
        $this->httpClient = $httpClient;
        $this->sanitizeHelper = $sanitizeHelper;
    }

    /**
     * @param array $files
     * @param int|null $dealerId
     * @return Collection|null
     */
    public function bulkUpload(array $files, ?int $dealerId = null): ?Collection
    {
        throw new NotImplementedException();
    }

    /**
     * @param string $type
     * @param array|string $identifiers
     * @return string
     */
    protected function getUploadDirectory(string $type, $identifiers): string
    {
        switch (strtolower($type)) {
            case self::UPLOAD_TYPE_WEBSITE_MEDIA:
            case self::UPLOAD_TYPE_IMAGE:
            case self::UPLOAD_TYPE_VIDEO:
            case self::UPLOAD_TYPE_CSV:
                break;
            default:
                $type = self::UPLOAD_TYPE_UNKNOWN;
                break;
        }

        $path = $type . DIRECTORY_SEPARATOR;

        if (empty($identifiers)) {
            return $path;
        }

        if (is_array($identifiers)) {
            foreach ($identifiers as $identifier) {
                $path .= CompactHelper::hash($identifier, 6) . DIRECTORY_SEPARATOR;
            }
            $path = rtrim($path, DIRECTORY_SEPARATOR);
        } else {
            $path .= CompactHelper::hash($identifiers, 6);
        }

        return $path;
    }

    /**
     * @param Filesystem $localDisk
     * @param int|null $dealerId
     * @param int|null $identifier
     * @return string
     */
    protected function getLocalFilename(Filesystem $localDisk, ?int $dealerId = null, ?int $identifier = null, ?string $extension = null): string
    {
        $dealerId = $dealerId ?? mt_rand(self::RAND_MIN, self::RAND_MAX);
        $identifier = $identifier ?? mt_rand(self::RAND_MIN, self::RAND_MAX);

        $uploadDirectory = $this->getUploadDirectory(self::UPLOAD_TYPE_IMAGE, [$dealerId, $identifier]);

        $localDisk->makeDirectory($uploadDirectory);

        if ($extension) {
            return sprintf(self::LOCAL_FILENAME_FORMAT, $uploadDirectory, CompactHelper::getRandomString(), $extension);
        }

        return sprintf(self::LOCAL_FILENAME_DEFAULT_FORMAT, $uploadDirectory, CompactHelper::getRandomString());
    }

    /**
     * @param Filesystem $localDisk
     * @param string $filename
     * @param string $fileContents
     * @return string
     * @throws FileUploadException
     */
    protected function saveLocalFile(Filesystem $localDisk, string $filename, string $fileContents): string
    {
        $result = $localDisk->put($filename, $fileContents);

        if (!$result) {
            throw new FileUploadException("Can't upload file. Filename - {$filename}");
        }

        return $localDisk->path($filename);
    }

    /**
     * @param string $filename
     * @param array|string $identifiers
     * @return string
     */
    protected function getS3Path(string $filename, $identifiers): string
    {
        $path = '';

        if (is_array($identifiers)) {

            foreach ($identifiers as $identifier) {
                if (!is_numeric($identifier)) {
                    $path .= $identifier;
                } else {
                    $path .= CompactHelper::hash($identifier, 6) . DIRECTORY_SEPARATOR;
                }
            }
            $path = rtrim($path, DIRECTORY_SEPARATOR);

        } else {

            $path .= CompactHelper::hash($identifiers, 6);

        }

        $path .= DIRECTORY_SEPARATOR . $filename;

        return $path;

    }

    /**
     * @param string $url
     * @param int|null $dealerId
     * @param int|null $identifier
     * @param bool $skipNotExisting
     * @return string
     *
     * @throws FileUploadException
     */
    protected function uploadLocalByUrl(string $url, ?int $dealerId = null, ?int $identifier = null, bool $skipNotExisting = false): ?string
    {
        $localDisk = Storage::disk('local_tmp');

        $filename = $this->getLocalFilename($localDisk, $dealerId, $identifier);

        if ($this->checkIfUrlIsFtp($url)) {
            $fileContents = file_get_contents($url);
        } else {
            $request = $this->httpClient->get($url, ['http_errors' => false]);
            if ($request->getStatusCode() === Response::HTTP_NOT_FOUND) {
                //try with the cdn url
                $url = str_replace(
                    parse_url($url, PHP_URL_HOST),
                    parse_url(env('CDN_URL'), PHP_URL_HOST),
                    $url
                );
                $request = $this->httpClient->get($url, ['http_errors' => false]);
            }
            $fileContents = $request->getBody()->getContents();
        }

        if (!$skipNotExisting && !$fileContents) {
            throw new FileUploadException("Can't get file contents. Url - {$url}, dealer_id - {$dealerId}, id - $identifier");
        }

        if ($skipNotExisting && !$fileContents) {
            return null;
        }

        return $this->saveLocalFile($localDisk, $filename, $fileContents);
    }

    /**
     * @param string $fileContents
     * @param Filesystem $localDisk
     * @param array $params
     * @return string|null
     *
     * @throws FileUploadException
     */
    protected function uploadLocalByContent(string $fileContents, Filesystem $localDisk, array $params): ?string
    {
        $dealerId = $params['dealer_id'] ?? null;
        $identifier = $params['identifier'] ?? null;
        $extension = $params['extension'] ?? null;

        $filename = $this->getLocalFilename($localDisk, $dealerId, $identifier, $extension);

        return $this->saveLocalFile($localDisk, $filename, $fileContents);
    }

    /**
     * @param string $localFilename
     * @param string $newFilename
     * @param int|null $dealerId
     * @param int|null $identifier
     * @param array $uploadParams
     * @return string
     *
     * @throws FileUploadException
     */
    public function uploadToS3(string $localFilename, string $newFilename, ?int $dealerId = null, ?int $identifier = null, array $uploadParams = []): string
    {
        $dealerId = $dealerId ?? mt_rand(self::RAND_MIN, self::RAND_MAX);
        $identifier = $identifier ?? mt_rand(self::RAND_MIN, self::RAND_MAX);

        $s3Filename = DIRECTORY_SEPARATOR . $this->getS3Path($newFilename, [$dealerId, $identifier]);

        $result = Storage::disk('s3')->put($s3Filename, file_get_contents($localFilename), $uploadParams);

        if (!$result) {
            throw new FileUploadException("Can't upload file to s3. File - {$localFilename}");
        }

        return $s3Filename;
    }

    /**
     * @param string $url
     * @return bool
     */
    public function checkIfUrlIsFtp(string $url): bool
    {
        return preg_match('/^ftp:\/\/[\da-z]+:[^\@]+\@\S+\s*$/', $url);
    }
}
