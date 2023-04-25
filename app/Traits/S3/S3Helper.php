<?php

namespace App\Traits\S3;

use Symfony\Component\HttpFoundation\Response;

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

    /**
     * Will only check the status code, when it is 200 or 300 it will return true
     */
    protected function exist(string $url): bool
    {
        $resource = curl_init();

        curl_setopt($resource, CURLOPT_URL, $url);
        curl_setopt($resource, CURLOPT_NOBODY, true);
        curl_setopt($resource, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($resource, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($resource, CURLOPT_TIMEOUT, 2);

        curl_exec($resource);

        $httpCode = curl_getinfo($resource, CURLINFO_HTTP_CODE);

        curl_close($resource);

        return $httpCode >= Response::HTTP_OK && $httpCode < Response::HTTP_MULTIPLE_CHOICES;
    }
}
