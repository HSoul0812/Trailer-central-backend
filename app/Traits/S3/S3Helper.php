<?php

namespace App\Traits\S3;

/**
 * Trait UrlHelper
 * @package App\Traits\S3
 */
trait S3Helper
{
    /**
     * @return string
     */
    protected function getS3BaseUrl(): string
    {
        if (!empty(env('CDN_URL_STORAGE'))) {
            return env('CDN_URL_STORAGE');
        }

        $urlMetadata = parse_url(env('AWS_URL'));

        return $urlMetadata['scheme'] . '://' . $urlMetadata['host'] . '/'.env('AWS_BUCKET');
    }

    /**
     * @param string $path
     * @return string
     */
    protected function getS3Url(string $path): string
    {
        return $this->getS3BaseUrl() . DIRECTORY_SEPARATOR . ltrim($path, '/');
    }
}
