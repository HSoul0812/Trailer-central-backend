<?php

namespace App\Services\CRM\Email;

use App\Helpers\ImageHelper;
use Illuminate\Http\UploadedFile;
use Imagick;
use Illuminate\Support\Facades\Storage;
use App\Traits\S3\S3Helper;

/**
 * Class MosaicoService
 *
 * @package App\Services\CRM\Email
 */
class MosaicoService implements MosaicoServiceInterface
{
    use S3Helper;

    /**
     * @var ImageHelper
     */
    private $imageHelper;

    /**
     * @var EmailBuilderServiceInterface
     */
    private $emailBuilderService;

    public function __construct(ImageHelper $imageHelper, EmailBuilderServiceInterface $emailBuilderService)
    {
        $this->imageHelper = $imageHelper;
        $this->emailBuilderService = $emailBuilderService;
    }

    /**
     * Resize Image OR
     * Get Image Placeholder OR
     * Generate Cover Image
     * 
     * @param array $params
     * 
     * @return string content of new image
     */
    public function processImage(array $params )
    {
        $fileUrl = $params['src'] ?? null;
        list($width, $height) = explode(',', $params['params']);
        $method = $params['method'];

        switch ($method) {
            case self::IMAGE_METHOD_PLACEHOLDER:
                return $this->imageHelper->getImagePlaceholder($width, $height);
            case self::IMAGE_METHOD_RESIZE:
                return $this->imageHelper->resizeImage($fileUrl, $width, $height);
            case self::IMAGE_METHOD_COVER:
            default:
                return $this->imageHelper->coverImage($fileUrl, $width, $height);
        }
    }

    /**
     * Upload Gallery Images and create Thumbnails of it
     * 
     * @param int $dealerId
     * @param array $files
     * 
     * @return array
     */
    public function uploadImages(int $dealerId, array $files)
    {
        $images = [];
        $galleryFolder = str_replace('{dealerId}', $dealerId, self::IMAGE_GALLERY_FOLDER);
        $thumbnailFolder = str_replace('{dealerId}', $dealerId, self::IMAGE_THUMBNAIL_FOLDER);

        foreach ($files as $file) 
        {
            if (!($file instanceof UploadedFile)) continue;

            // upload image
            $fileContent = $file->get();
            $fileName = $file->getClientOriginalName();
            $filePath = $galleryFolder .'/'. $fileName;
            Storage::disk('s3')->put($filePath, $fileContent);
            $fileUrl = Storage::disk('s3')->url($filePath);

            // create thumbnail
            $tempThumbnailPath = $this->imageHelper->createThumbnailImage($fileUrl);

            // upload thumbnail
            $thumbnailContent = file_get_contents($tempThumbnailPath);
            $thumbnailPath = $thumbnailFolder .'/'. $fileName;
            Storage::disk('s3')->put($thumbnailPath, $thumbnailContent);
            unlink($tempThumbnailPath);

            // append data
            $images[] = [
                'name' => $fileName,
                'url' => $fileUrl,
                'size' => $this->imageHelper->getRemoteFileSize($fileUrl),
                'thumbnailUrl' => $this->getS3Url($thumbnailPath)
            ];
        }

        return $images;
    }

    /**
     * Get Gallery Images and its Thumbnails
     * 
     * @param int $dealerId
     * 
     * @return array
     */
    public function getImages(int $dealerId)
    {
        $images = [];
        $galleryFolder = str_replace('{dealerId}', $dealerId, self::IMAGE_GALLERY_FOLDER);
        $thumbnailFolder = str_replace('{dealerId}', $dealerId, self::IMAGE_THUMBNAIL_FOLDER);

        $allImages = Storage::disk('s3')->allFiles($galleryFolder);

        foreach ($allImages as $filePath) {

            $imageUrl = $this->getS3Url($filePath);
            $filename = basename($filePath);

            $images[] = [
                'name' => $filename,
                'url' => $imageUrl,
                'size' => $this->imageHelper->getRemoteFileSize($imageUrl),
                'thumbnailUrl' => $this->getS3Url($thumbnailFolder .'/'. $filename)
            ];
        }

        return $images;
    }

    /**
     * Test Send Email with given Html
     * 
     * @param array $params
     */
    public function send(array $params)
    {
        $html = $this->convertHtmlImages($params['dealer_id'], $params['html']);

        return $this->emailBuilderService->testTemplate(
            $params['dealer_id'], 
            $params['user_id'], 
            $params['subject'], 
            $html,
            $params['rcpt']
        );
    }

    /**
     * Upload Images with Resize Method into S3
     * 
     * @param int $dealerId
     * @param string $html
     * 
     * @return string of Html
     */
    protected function convertHtmlImages(int $dealerId, string $html)
    {
        $matches = [];
        $num_full_pattern_matches = preg_match_all( '#<img.*?src="([^"]*?\/[^/]*\.[^"]+)#i', $html, $matches );

        $galleryFolder = str_replace('{dealerId}', $dealerId, self::IMAGE_GALLERY_FOLDER);
        $staticFolder = str_replace('{dealerId}', $dealerId, self::IMAGE_STATIC_FOLDER);

        // Get All Images
        for ( $i = 0; $i < $num_full_pattern_matches; $i++ ) {
            if ( stripos( $matches[ 1 ][ $i ], "/img?src=" ) !== FALSE ) {
                $src_matches = [];

                // Match Images
                if ( preg_match( '#/img\?src=(.*)&amp;method=(.*)&amp;params=(.*)#i', $matches[ 1 ][ $i ], $src_matches ) !== FALSE ) {
                    $file_name = $fileUrl = urldecode( $src_matches[ 1 ] );
                    $file_name = substr( $file_name, strlen( $this->getS3Url($galleryFolder) ) );

                    $method = urldecode( $src_matches[ 2 ] );

                    $params = urldecode( $src_matches[ 3 ] );
                    $params = explode( ",", $params );
                    $width = (int) $params[ 0 ];
                    $height = (int) $params[ 1 ];

                    $static_file_name = $method . "_" . $width . "x" . $height . "_" . $file_name;

                    $html = str_ireplace( $matches[ 1 ][ $i ], $this->getS3Url($staticFolder) .'/'. urlencode( $static_file_name ), $html );
                    
                    $imageContent = $this->processImage([
                        'method' => $method,
                        'src' => $fileUrl,
                        'params' => $width .',', $height
                    ]);

                    Storage::disk('s3')->put($staticFolder .'/'. $static_file_name, $imageContent);
                }
            }
        }

        return $html;
    }

    /**
     * List Mosaico Configurations
     * 
     * @param int $dealerId
     * 
     * @return array
     */
    public function getConfigs(int $dealerId)
    {
        $galleryFolder = str_replace('{dealerId}', $dealerId, self::IMAGE_GALLERY_FOLDER);
        $thumbnailFolder = str_replace('{dealerId}', $dealerId, self::IMAGE_THUMBNAIL_FOLDER);
        $staticFolder = str_replace('{dealerId}', $dealerId, self::IMAGE_STATIC_FOLDER);

        return [
            'THUMBNAIL_WIDTH' => self::THUMBNAIL_WIDTH,
            'THUMBNAIL_HEIGHT' => self::THUMBNAIL_HEIGHT,
            'IMAGE_GALLERY_FOLDER' => $galleryFolder,
            'IMAGE_THUMBNAIL_FOLDER' => $thumbnailFolder,
            'IMAGE_STATIC_FOLDER' => $staticFolder
        ];
    }
}