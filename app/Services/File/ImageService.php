<?php

namespace App\Services\File;

use App\Exceptions\File\FileUploadException;
use App\Exceptions\File\ImageUploadException;
use App\Exceptions\Helpers\MissingOverlayLogoParametersException;
use App\Exceptions\Helpers\StorageFileException;
use App\Helpers\ImageHelper;
use App\Helpers\SanitizeHelper;
use App\Services\File\DTOs\FileDto;
use App\Traits\CompactHelper;
use App\Traits\S3\S3Helper;
use GuzzleHttp\Client;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\User\User;
use InvalidArgumentException;
use Illuminate\Support\Facades\Log;
use App;

/**
 * Class ImageService
 * @package App\Services\File
 */
class ImageService extends AbstractFileService
{
    use S3Helper;

    /**
     * @var float 0.2 seconds, it means it may send up to five S3 request per second
     *                (we need to consider multiply by the number of queue workers)
     */
    const WAIT_FOR_INVENTORY_IMAGE_GENERATION_IN_MICROSECONDS = 200 * 1000;

    /** @var ImageHelper */
    private $imageHelper;

    /** @var \Psr\Log\LoggerInterface  */
    private $log;

    public const DEFAULT_EXTENSION = 'jpg';

    public const EXTENSION_MAPPING = [
        'image/gif' => 'gif',
        'image/jpeg' => 'jpg',
        'image/jpg' => 'jpg',
        'image/png' => 'png',
        'image/x-png' => 'png',
        'image/x-MS-bmp' => 'bmp',
        'image/x-ms-bmp' => 'bmp',
        'image/x-portable-bitmap' => 'pbm',
        'image/x-photo-cd' => 'pcd',
        'image/x-pict' => 'pic',
        'image/tiff' => 'tiff'
    ];

    public function __construct(Client $httpClient, SanitizeHelper $sanitizeHelper, ImageHelper $imageHelper)
    {
        parent::__construct($httpClient, $sanitizeHelper);

        $this->imageHelper = $imageHelper;
        $this->log = Log::channel('images');
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
    public function upload(
        string $url,
        string $title,
        ?int $dealerId = null,
        ?int $identifier = null,
        array $params = []
    ): ?FileDto
    {
        $skipNotExisting = $params['skipNotExisting'] ?? false;

        $localFilename = $this->uploadLocalByUrl($url, $dealerId, $identifier, $skipNotExisting);

        if ($localFilename) {
            $imageInfo = getimagesize($localFilename);
        }

        if (isset($imageInfo['mime']) && in_array($imageInfo['mime'], array_keys(self::EXTENSION_MAPPING))) {
            $extension = self::EXTENSION_MAPPING[$imageInfo['mime']];
        } else {
            $extension = pathinfo($url, PATHINFO_EXTENSION);
        }

        if (empty($extension)) {
            $extension = self::DEFAULT_EXTENSION;
        }

        $filenameParts = [
            $title,
            CompactHelper::getRandomString(),
            $extension
        ];

        // a name like `test_image_title_2Y3gR6argueTroll.jpg`
        $inventoryFilenameTitle = sprintf('%s_%s.%s', ...$filenameParts);

        $s3Filename = $this->sanitizeHelper->cleanFilename($inventoryFilenameTitle);

        try {
            if ($localFilename) {
                $this->imageHelper->resize($localFilename, 800, 800, true);

                $s3Path = $this->uploadToS3($localFilename, $s3Filename, $dealerId, $identifier, $params);

                $hash = sha1_file($localFilename);
                unlink($localFilename);

                return new FileDto($s3Path, $hash);
            }
        } catch (\Exception $ex) {
            $this->log->error($ex->getMessage().': '.$ex->getTraceAsString());
        }

        return null;
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
            throw new ImageUploadException('file has been missed');
        }

        $file = $data['file'];

        if (!in_array($file->getMimeType(), array_keys(self::EXTENSION_MAPPING))) {
            throw new ImageUploadException('Not expected mime type');
        }

        $content = $file->get();

        $params['dealer_id'] = $data['dealer_id'] ?? null;
        $params['extension'] = self::EXTENSION_MAPPING[$file->getMimeType()];

        $localDisk = Storage::disk('local_tmp');

        $localFilename = $this->uploadLocalByContent($content, $localDisk, $params);

        $hash = sha1_file($localFilename);

        $url = $localDisk->url(str_replace($localDisk->path(''), '', $localFilename));

        return new FileDto($localFilename, $hash, null, $url);
    }

    /**
     * Add Text and Logo Overlays to image
     *
     * @param  string  $imagePath
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
     * @return string|null local path of new image, or null when new image and original image are same
     *
     * @throws MissingOverlayLogoParametersException when logo overlay is enabled and its configurations
     *                                               were not provided
     */
    public function addOverlays(string $imagePath, array $params)
    {
        $imagePath = $this->imageHelper->encodeUrl($imagePath);

        // when the image has been imported from production it will not be available in the staging/dev bucket
        // so we need to check if the image exists, if not we gonna use the production S3 bucket base URL
        if (!App::environment('production') && !App::runningUnitTests() && !$this->exist($imagePath)) {
            $imagePath = str_replace(config('services.aws.url'), $this->getProductionS3BaseUrl(), $imagePath);
        }

        $originalImagePath = $imagePath;
        $tempFiles = [];

        $logoUpperPositions = [User::OVERLAY_LOGO_POSITION_UPPER_LEFT, User::OVERLAY_LOGO_POSITION_UPPER_RIGHT];

        // Add Upper Text Overlay if applicable
        if (in_array($params['overlay_upper'], User::OVERLAY_TEXT_SETTINGS)
            && !in_array($params['overlay_logo_position'], $logoUpperPositions)) {

            $upperText = $params['overlay_text_'. $params['overlay_upper']];
            $imagePath = $this->imageHelper->addUpperTextOverlay($imagePath, $upperText, $params);
            $tempFiles[] = $imagePath;
        }

        $logoLowerPositions = [User::OVERLAY_LOGO_POSITION_LOWER_LEFT, User::OVERLAY_LOGO_POSITION_LOWER_RIGHT];

        // Add Lower Text Overlay if applicable
        if (in_array($params['overlay_lower'], User::OVERLAY_TEXT_SETTINGS)
            && !in_array($params['overlay_logo_position'], $logoLowerPositions)) {

            $lowerText = $params['overlay_text_'. $params['overlay_lower']];
            $imagePath = $this->imageHelper->addLowerTextOverlay($imagePath, $lowerText, $params);
            $tempFiles[] = $imagePath;
        }

        // Add Logo Overlay if applicable
        if ($params['overlay_logo_position'] !== User::OVERLAY_LOGO_POSITION_NONE
            && !empty($params['overlay_logo'])) {

            $logoPath = $this->imageHelper->encodeUrl($params['overlay_logo']);
            $imagePath = $this->imageHelper->addLogoOverlay($imagePath, $logoPath, $params);
        }

        // If No Overlays are applied
        if ($imagePath === $originalImagePath) {
            return null;
        }

        // Delete Unused Temp Files
        $tempFiles = array_diff($tempFiles, [$imagePath]);

        foreach ($tempFiles as $file) {
            unlink($file);
        }

        return $imagePath;
    }

    /**
     * Adds overlay to an image and save it to the storage
     *
     * @param  string  $originalFilename
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
     *     }  $overlayConfig
     *
     * @return string the new filename
     *
     * @throws MissingOverlayLogoParametersException when logo overlay is enabled and its configurations
     *                                               were not provided
     * @throws FileUploadException when an image was not save in remote storage
     * @throws StorageFileException when an image was not save in local storage
     */
    public function addOverlayAndSaveToStorage(string $originalFilename, array $overlayConfig): string
    {
        if (empty($overlayConfig['dealer_id'])) {
            throw new InvalidArgumentException(
                sprintf("[%s::addOverlayAndSaveToStorage] 'dealer_id' was not provided.", __CLASS__)
            );
        }

        $localNewImagePath = $this->addOverlays($this->getS3BaseUrl().$originalFilename, $overlayConfig);

        if (empty($localNewImagePath)) {
            throw new StorageFileException;
        }

        // setup new filename for the image overlay
        $filenameParts = explode('.', basename($originalFilename));
        $overlayFilename = $filenameParts[0].('_overlay_'.time());

        if (count($filenameParts) > 1) {
            $overlayFilename .= '.'.$filenameParts[1];
        }

        try {
            $filename = $this->uploadToS3($localNewImagePath, $overlayFilename, $overlayConfig['dealer_id']);

            unlink($localNewImagePath);
        } catch (\Exception $exception) {
            unlink($localNewImagePath);

            throw $exception;
        }

        return $filename;
    }
}
