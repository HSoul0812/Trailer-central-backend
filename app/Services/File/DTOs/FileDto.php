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
     * FileDto constructor.
     * @param string $path
     * @param string|null $hash
     * @param string|null $mimeType
     */
    public function __construct(string $path, ?string $hash = null, ?string $mimeType = null)
    {
        $this->path = $path;
        $this->hash = $hash;
        $this->mimeType = $mimeType;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getHash(): ?string
    {
        return $this->hash;
    }

    /**
     * @return string
     */
    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }
}
