<?php

namespace App\Traits;

use App\Models\User\Dealer;
use Aws\S3\S3Client;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Config;
use App\Traits\CompactHelper;

interface UploadConst {
    const UPLOAD_TYPE_WEBSITE_MEDIA = 'website/media';
    const UPLOAD_TYPE_IMAGE = "media";
    const UPLOAD_TYPE_VIDEO = "media";
    const UPLOAD_TYPE_CSV = "uploads";
    const UPLOAD_TYPE_UNKNOWN = "uploads/abbandoned";

    const UPLOAD_PATH = "/var/www/vhosts/trailercentral.com/html";

    const DS = "/";
}

trait UploadHelper
{
    public function uploadImage($file, $dealerIdentifier) {

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
        $logger = new Logger;
        $logger->getLogger('resources')->info("DEALER UPLOADS POST: dealer {$dealerIdentifier}", $this);

        $dealerModel = Dealer::dealerByIdentifier($dealerIdentifier);

        if($dealerModel) {

            $filepath = self::getUploadDirectory(UploadConst::UPLOAD_TYPE_CSV, array( $dealerModel->getId() ));

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
                    $logger->getLogger('resources')->error("Could not download file from '{$url}'. Reason: " . $e->getMessage(), $this);

                    $code = Tonic_Response::PRECONDITIONFAILED;

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
                        $logger->getLogger('resources')->error("Could not save file to '{$filename}'. Reason: " . $e->getMessage(), $this);

                        $code = Tonic_Response::INTERNALSERVERERROR;

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
                            $logger->getLogger('resources')->info("File '{$currentFilename}' renamed to '{$filename}'.", $this);

                        } else {
                            $logger->getLogger('resources')->error("File '{$currentFilename}' could not be moved to '{$filename}'.", $this);

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
                        $logger->getLogger('resources')->error("File '{$currentFilename}' could not be moved to '{$filename}'. Reason: " . $e->getMessage(), $this);

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
                    $logger->getLogger('resources')->error("Uploaded file '{$currentFilename}' could not be found.", $this);

                    $code = Tonic_Response::PRECONDITIONFAILED;

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
                $logger->getLogger('requests')->error('No URL or FILENAME specified for uploaded file.', $this);

                $code = Tonic_Response::PRECONDITIONFAILED;

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
                $logger->getLogger('resources')->error("Uploaded file '{$filename}' could not be found.", $this);

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
                $logger->getLogger('resources')->debug("Uploaded file '{$filename}' found.", $this);
            }


            // TODO: implement Upload Model and DealerUpload model to finish this code block
//            if(file_exists($filename)) {
//                try {
//                    // add entry in the database
//                    $uploadModel = new Model_Upload();
//                    $uploadModel->setData('filename', $filename);
//                    $uploadModel->setData('title', $title);
//                    $uploadModel->setData('hash', sha1_file($filename));
//                    $uploadModel->save();
//
//                    $uploadId = $uploadModel->getId();
//
//                    // rename the file to include correct extension
//                    $newfilename = $filepath . UploadConst::DS . Helper_Compact::hash($uploadId) . ".csv";
//                    rename($filename, $newfilename);
//                    $uploadModel->setData('filename', $newfilename);
//                    $uploadModel->save();
//
//                    $uploadDealerModel = new Model_Dealer_Upload();
//                    $uploadDealerModel->setData('dealer_id', $dealerModel->getId());
//                    $uploadDealerModel->setData('upload_id', $uploadId);
//                    $uploadDealerModel->setData('is_parts_upload', '1');
//                    $uploadDealerModel->save();
//                    $uploadIdentifier = Helper_Compact::shorten($uploadModel->getId());
//
//                    $uploadData = array();
//
//                    $uploadData[] = array(
//                        'identifier'     => $uploadIdentifier,
//                        'created_at'     => $uploadModel->getData('created_at'),
//                        'title'          => $uploadModel->getData('title'),
//                        'url'            => Helper_Url::getSiteFileUrl($uploadModel->getData('filename')),
//                        'last_run_at'    => null,
//                        'last_run_state' => 'not run'
//                    );
//
//                    $responseData['status'] = "success";
//                    $responseData['upload'] = $uploadData;
//                    $code         = Tonic_Response::CREATED;
//
//                    $logger->getLogger('resources')->info("Added upload '{$uploadIdentifier}' to dealer '{$dealerIdentifier}'", $this);
//                } catch(Exception $e) {
//                    $logger->getLogger('resources')->error($e, $this);
//
//                    $code = Tonic_Response::INTERNALSERVERERROR;
//                }
//            }
        } else {
            $logger->getLogger('resources')->warn("Dealer '{$dealerIdentifier}' not found in DB.", $this);
        // TODO define constants
//            $link = BASE_URL . '/' . API_VERSION . HTTP::ERROR_LINK;
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
    public function getS3Path($filename, $identifiers) {

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

    public function putImageToS3($sourcePath, $path, $imageType) {

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
        $logger = new Logger;

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
                    $logger->getLogger('resources')->error("Could not create directory '{$pathpart}' in '{$currentpath}'. Reason: " . $e->getMessage(), $this);
                }
            }
            $currentpath .= $pathpart . UploadConst::DS;
        }
    }

    public function makeWriteable($file, $chmod = 0755) {
        $fileowner = fileowner(UploadConst::UPLOAD_PATH);
        $logger = new Logger;
        try {
            chmod($file, $chmod);
            chown($file, $fileowner);
        } catch(Exception $e) {
            $logger->getLogger('resources')->error("Could not set rights to '{$file}'. Reason: " . $e->getMessage(), $this);
        }
    }
}
