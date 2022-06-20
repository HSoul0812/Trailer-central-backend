<?php

namespace App\Services\File\DTOs;

/**
 * Class FileDto
 * @package App\Services\File\DTOs
 */
class FileDto
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var string|null
     */
    private $hash;

    /**
     * @var string|null
     */
    private $mimeType;

    /**
     * @var string|null
     */
    private $url;

    /**
     * FileDto constructor.
     * @param string $path
     * @param string|null $hash
     * @param string|null $mimeType
     * @param string|null $url
     */
    public function __construct(string $path, ?string $hash = null, ?string $mimeType = null, ?string $url = null)
    {
        $this->path = $path;
        $this->hash = $hash;
        $this->mimeType = $mimeType;
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string|null
     */
    public function getHash(): ?string
    {
        return $this->hash;
    }

    /**
     * @return string|null
     */
    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }
}
