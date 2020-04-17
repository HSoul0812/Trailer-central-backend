<?php

namespace App\Traits;

use App\Models\Interactions\DealerUpload;
use App\Models\Upload\Image;
use App\Models\User\Dealer;
use App\Models\Upload\Upload;
use Aws\S3\S3Client;
use Illuminate\Support\Facades\Config;
use App\Traits\CompactHelper;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

interface UploadConst {
    const UPLOAD_TYPE_WEBSITE_MEDIA = 'website/media';
    const UPLOAD_TYPE_IMAGE = "media";
    const UPLOAD_TYPE_VIDEO = "media";
    const UPLOAD_TYPE_CSV = "uploads";
    const UPLOAD_TYPE_UNKNOWN = "uploads/abbandoned";

    const UPLOAD_PATH = "/var/www/vhosts/trailercentral.com/html";

    const DS = "/";
    const API_VERSION = 'v1.1';
}

trait UploadHelper
{
    public static function uploadImage($file, $dealerIdentifier, $mimeType) {

        $bucket = env('AWS_BUCKET');

        $s3 = S3Client::factory([
            'version'     => 'latest',
            'region'      => env('AWS_DEFAULT_REGION'),
            'credentials' =>
                array(
                    'key'       => env('AWS_ACCESS_KEY_ID'),
                    'secret'    => env('AWS_SECRET_ACCESS_KEY')
                )
        ]);

        // append images to existing inventory
        $responseData = array();
        $code = 0;

        $dealerModel = Dealer::findOrFail($dealerIdentifier);

        if($dealerModel) {

            $filepath = self::getUploadDirectory(UploadConst::UPLOAD_TYPE_CSV, array( $dealerIdentifier ));

            // create directory, if it doesn't already exist
            self::createDirectory($filepath, 0775);

            $tempname = CompactHelper::hash(time()) . base_convert(rand(1, getrandmax()), 10, 36);
            $filename = $filepath . UploadConst::DS . $tempname . ".tmp";

            $now   = new DateTime();
            $title = (isset($file['title'])) ? $file['title'] : 'Inventory Data uploaded at ' . $now->format('D j M Y g:i');

            if(isset($file['url'])) {
                $url = $file->getPathname();

                try {
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

                    $filecontents = curl_exec($ch);

                    curl_close($ch);
                } catch(Exception $e) {
                    Log::error("Could not download file from '{$url}'. Reason: " . $e->getMessage());

                    $code = HTTP::PRECONDITIONFAILED;

                    $errors           = array();
                    $errorDescription = "Could not download file from '{$url}'.";
                    $errors[]         = array(
                        'code'        => HTTP::OBJECT_NOT_FOUND,
                        'description' => $errorDescription
                    );

                    $responseData['status'] = "error";
                    $responseData['errors'] = $errors;
                }

                if(!empty($filecontents)) {
                    try {
                        // save the file
                        $result = $s3->putObject(array(
                            'Bucket'       => $bucket,
                            'Key'          => 'distillery-trailercentral',
                            'SourceFile'   => $filecontents,
                            'ContentType'  => 'text/plain',
                            'ACL'          => 'public-read',
                            'StorageClass' => 'REDUCED_REDUNDANCY'
                        ));
                        //file_put_contents($filename, $filecontents);
                    } catch(Exception $e) {
                        Log::error("Could not save file to '{$filename}'. Reason: " . $e->getMessage());

                        $code = HTTP::INTERNALSERVERERROR;

                        $errors           = array();
                        $errorDescription = "An unknown error occured.";
                        $errors[]         = array(
                            'code'        => HTTP::OBJECT_UNKNOWN,
                            'description' => $errorDescription
                        );

                        $responseData['status'] = "error";
                        $responseData['errors'] = $errors;
                    }
                }
            } else if(!!$file->getClientOriginalName()) {
                // move the file
                $currentFilename = $file->getClientOriginalName();

                if(file_exists($currentFilename)) {

                    try {
                        $result = rename($currentFilename, $filename);
                        if($result) {
                            Log::info("File '{$currentFilename}' renamed to '{$filename}'.");

                        } else {
                            Log::error("File '{$currentFilename}' could not be moved to '{$filename}'.");

                            $code = HTTP::INTERNALSERVERERROR;

                            $errors           = array();
                            $errorDescription = "An unknown error occured.";
                            $errors[]         = array(
                                'code'        => HTTP::OBJECT_UNKNOWN,
                                'description' => $errorDescription
                            );

                            $responseData['status'] = "error";
                            $responseData['errors'] = $errors;
                        }
                    } catch(Exception $e) {
                        Log::error("File '{$currentFilename}' could not be moved to '{$filename}'. Reason: " . $e->getMessage());

                        $code = HTTP::INTERNALSERVERERROR;

                        $errors           = array();
                        $errorDescription = "An unknown error occured.";
                        $errors[]         = array(
                            'code'        => HTTP::OBJECT_UNKNOWN,
                            'description' => $errorDescription
                        );

                        $responseData['status'] = "error";
                        $responseData['errors'] = $errors;
                    }
                } else {
                    Log::error("Uploaded file '{$currentFilename}' could not be found.");

                    $code = HTTP::PRECONDITIONFAILED;

                    $errors           = array();
                    $errorDescription = "Uploaded file '{$currentFilename}' could not be found.";
                    $errors[]         = array(
                        'code'        => HTTP::OBJECT_NOT_FOUND,
                        'description' => $errorDescription
                    );

                    $responseData['status'] = "error";
                    $responseData['errors'] = $errors;
                }
            } else {
                Log::error('No URL or FILENAME specified for uploaded file.');

                $code = HTTP::PRECONDITIONFAILED;

                $errors           = array();
                $errorDescription = "URL or FILENAME not specified for uploaded file.";
                $errors[]         = array(
                    'code'        => HTTP::OBJECT_NOT_FOUND,
                    'description' => $errorDescription
                );

                $responseData['status'] = "error";
                $responseData['errors'] = $errors;
            }

            if(!file_exists($filename)) {
                Log::error("Uploaded file '{$filename}' could not be found.");

                $code = HTTP::INTERNALSERVERERROR;

                $errors           = array();
                $errorDescription = "Uploaded file '{$filename}' not found.";
                $errors[]         = array(
                    'code'        => HTTP::OBJECT_NOT_FOUND,
                    'description' => $errorDescription
                );

                $responseData['status'] = "error";
                $responseData['errors'] = $errors;
            } else {
                Log::debug("Uploaded file '{$filename}' found.");
            }

            if(file_exists($filename)) {
                try {
                    // add entry in the database
                    $uploadModel = new Upload();
                    $uploadModel->filename = $filename;
                    $uploadModel->title = $title;
                    $uploadModel->hash = sha1_file($filename);
                    $uploadModel->save();

                    // rename the file to include correct extension
                    $newfilename = $filepath . UploadConst::DS . CompactHelper::hash($uploadModel->id) . ".csv";
                    rename($filename, $newfilename);
                    $uploadModel->filename = $newfilename;
                    $uploadModel->save();

                    $uploadDealerModel = new DealerUpload();
                    $uploadDealerModel->dealer_id = $dealerIdentifier;
                    $uploadDealerModel->upload_id = $uploadModel->id;
                    $uploadDealerModel->is_parts_upload = '1';
                    $uploadDealerModel->save();
                    $uploadIdentifier = CompactHelper::shorten($uploadModel->id);

                    $uploadData = array();

                    $uploadData[] = array(
                        'identifier'     => $uploadIdentifier,
                        'created_at'     => $uploadModel->created_at,
                        'title'          => $uploadModel->title,
                        'url'            => UrlHelper::getSiteFileUrl($uploadModel->filename),
                        'last_run_at'    => null,
                        'last_run_state' => 'not run'
                    );

                    $responseData['status'] = "success";
                    $responseData['upload'] = $uploadData;
                    $code         = HTTP::CREATED;

                    $path = UploadHelper::getS3Path($newfilename, array($dealerIdentifier, $inventoryId));
                    $result = UploadHelper::putImageToS3($filename, $path, $mimeType);
                    unlink($filename);

                    Log::info("Added upload '{$uploadIdentifier}' to dealer '{$dealerIdentifier}'");
                } catch(Exception $e) {
                    Log::error($e);

                    $code = HTTP::INTERNALSERVERERROR;
                }
            }
        } else {
            Log::warning("Dealer '{$dealerIdentifier}' not found in DB.");
            $errors           = array();
            $errorDescription = "Dealer '{$dealerIdentifier}' not found in DB.";
            $errors[]         = array(
                'code'        => HTTP::PRECONDITIONFAILED,
                'description' => $errorDescription
            );

            $responseData['status'] = "error";
            $responseData['errors'] = $errors;
            $code = HTTP::PRECONDITIONFAILED;
        }

        return response(json_encode(array( 'response' => $responseData )), $code)
            ->header('Content-type', 'application/json');
//            ->header('Link', $link);
    }

    public function getUploadDirectory($type, $identifiers) {
        switch(strtolower($type)) {
            case UploadConst::UPLOAD_TYPE_WEBSITE_MEDIA:
            case UploadConst::UPLOAD_TYPE_IMAGE:
            case UploadConst::UPLOAD_TYPE_VIDEO:
            case UploadConst::UPLOAD_TYPE_CSV:
                break;
            default:
                $type = UploadConst::UPLOAD_TYPE_UNKNOWN;
                break;
        }

        $path = UploadConst::UPLOAD_PATH . UploadConst::DS . $type . UploadConst::DS;

        if(empty($identifiers)) {
            return $path;
        }

        if(is_array($identifiers)) {
            foreach($identifiers as $identifier) {
                $path .= CompactHelper::hash($identifier, 6) . UploadConst::DS;
            }
            $path = rtrim($path, UploadConst::DS);
        } else {
            $path .= CompactHelper::hash($identifiers, 6);
        }

        return $path;
    }
    public static function getS3Path($filename, $identifiers) {

        $path = '';

        if(is_array($identifiers)) {

            foreach($identifiers as $identifier) {
                $path .= CompactHelper::hash($identifier, 6) . UploadConst::DS;
            }
            $path = rtrim($path, UploadConst::DS);

        } else {

            $path .= CompactHelper::hash($identifiers, 6);

        }

        $path .= UploadConst::DS . $filename;

        return $path;

    }
    public static function putImageToS3($sourcePath, $path, $imageType) {

        $bucket = env('AWS_BUCKET');

        return S3Helper::putFile($sourcePath, $bucket, $path, 'public-read', $imageType);

    }
    public function moveFile($from = null, $to = null) {

        if(empty($from) || empty($to)) {
            return;
        }

        try {

            $result = rename($from, $to);

        } catch(Exception $e) {

            $result = $e;

        }

        return $result;

    }
    public function createDirectory($directory, $chmod = 0755) {
        // recursively create the directories until
        $directory = str_replace(UploadConst::UPLOAD_PATH, '', $directory);
        $directory = ltrim($directory, UploadConst::DS);
        $path = explode(UploadConst::DS, $directory);

        $currentpath = UploadConst::UPLOAD_PATH . UploadConst::DS;
        foreach($path as $pathpart) {
            if(!file_exists($currentpath . $pathpart)) {
                try {
                    $fileowner = fileowner(UploadConst::UPLOAD_PATH);
                    if(mkdir($currentpath . $pathpart)) {
                        chmod($currentpath . $pathpart, $chmod);
                        @chown($currentpath . $pathpart, $fileowner);
                    } else {
                        throw new Exception("Could not create directory '{$pathpart}' in '{$currentpath}'.");
                    }
                } catch(Exception $e) {
                    Log::error("Could not create directory '{$pathpart}' in '{$currentpath}'. Reason: " . $e->getMessage());
                }
            }
            $currentpath .= $pathpart . UploadConst::DS;
        }
    }
    public function makeWriteable($file, $chmod = 0755) {
        $fileowner = fileowner(UploadConst::UPLOAD_PATH);
        try {
            chmod($file, $chmod);
            chown($file, $fileowner);
        } catch(Exception $e) {
            Log::error("Could not set rights to '{$file}'. Reason: " . $e->getMessage());
        }
    }

    static function uploadImages($images, $dealerId, $inventoryId, $inventoryTitle) {
        if(empty($images)) {
            return;
        }
        $filepath = self::getUploadDirectory(UploadConst::UPLOAD_TYPE_IMAGE, array(
            $dealerId,
            $inventoryId
        ));
        // create directory, if it doesn't already exist
        self::createDirectory($filepath, 0775);
        $tempname = CompactHelper::hash(time()) . base_convert(rand(1, getrandmax()), 10, 36);
        $filename = $filepath . DS . $tempname . ".tmp";
        $extension = "";
        foreach($images as $url) {
            Log::debug("ATTEMPTING IMAGE: $url");

            $filecontents = '';
            try {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYSTATUS, false);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                $filecontents = curl_exec($ch);
                curl_close($ch);
            } catch(Exception $e) {

            }
            if(!empty($filecontents) && $filecontents !== false) {
                try {
                    // save the file
                    file_put_contents($filename, $filecontents);
                } catch(Exception $e) {
                }
            }
            if(file_exists($filename)) {
                $imageinfo = getimagesize($filename);
                $mimetype = $imageinfo['mime'];
                $extension = "";
                switch($mimetype) {
                    case "image/gif":
                        $extension = "gif";
                        break;
                    case "image/jpeg":
                        $extension = "jpg";
                        break;
                    case "image/png":
                        $extension = "png";
                        break;
                    default:
                        $extension = "";
                        break;
                }
                if($extension != "") {
                    // add entry in the database
                    $imageModel = new Image();
                    $imageModel->filename = $filename;
                    $imageModel->hash = sha1_file($filename);
                    $imageId = $imageModel->save();
                    $inventoryFilenameTitle = $inventoryTitle . "_" . CompactHelper::hash($imageId) . ".{$extension})";
                    $newfilename = preg_replace(array('/\s/', '/\.[\.]+/', '/[^\w_\.\-]/'), array(
                        '_',
                        '.',
                        ''
                    ), $inventoryFilenameTitle);
                    Helper_Image::resize($filename, 800, 800, true, $filename);
                    $path = self::getS3Path($newfilename, array($dealerId, $inventoryId));
                    $result = self::putImageToS3($filename, $path, $mimetype);
                    unlink($filename);
                    $imageModel->setData('filename', '/' . $path);
                    $imageModel->save();
                    $imageInventoryModel = new Model_Inventory_Image();
                    $imageInventoryModel->setData('inventory_id', $inventoryId);
                    $imageInventoryModel->setData('image_id', $imageId);
                    $imageInventoryModel->save();
                }
            }
        }

    }
}
