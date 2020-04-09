<?php

namespace App\Traits;

use Illuminate\Support\Facades\Config;


trait S3Helper
{
    public function putFile($source = null, $bucket = null, $path = null, $acl = 'public-read', $imageType = null) {

        if(empty($source) || empty($bucket) || empty($path) || empty($imageType)) {
            return false;
        }

        $source = file_get_contents($source);

        $result = S3_Api::putObject($source, $bucket, $path, $acl, array(), $imageType);

        return $result;

    }

    public function putFileContents($source = null, $bucket = null, $path = null, $acl = 'public-read', $imageType = null) {

        if(empty($source) || empty($bucket) || empty($path) || empty($imageType)) {
            return false;
        }

        $result = S3_Api::putObject($source, $bucket, $path, $acl, array(), $imageType);

        return $result;

    }

    public function buildFile($sourcePath = null) {

        return S3_Api::inputFile($sourcePath);

    }
}
