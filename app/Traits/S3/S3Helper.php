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
        if (!empty(config('app.cdn_storage_url'))) {
            return config('app.cdn_storage_url');
        }

        $urlMetadata = parse_url(config('services.aws.url'));

        return $urlMetadata['scheme'] . '://' . $urlMetadata['host'] . '/'.config('services.aws.bucket');
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
