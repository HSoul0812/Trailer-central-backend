<?php

namespace App\Services\Integration\Common\DTOs;

/**
 * Class AttachmentFile
 * 
 * @package App\Services\Integration\Common\DTOs
 */
class AttachmentFile
{
    /**
     * @var string Temporary Local Filename
     */
    private $tmpName;

    /**
     * @var string Path to Current File
     */
    private $filePath;

    /**
     * @var string Filename to Original File
     */
    private $fileName;

    /**
     * @var int Size of Current File
     */
    private $fileSize;

    /**
     * @var string Mime Type of Current File
     */
    private $mimeType;

    /**
     * @var string Contents of Current File
     */
    private $contents;


    /**
     * Return Temp File Name
     * 
     * @return string $this->tmpName
     */
    public function getTmpName(): string
    {
        return $this->tmpName;
    }

    /**
     * Set Temp File Name
     * 
     * @param string $tmpName
     * @return void
     */
    public function setTmpName(string $tmpName): void
    {
        $this->tmpName = $tmpName;
    }


    /**
     * Return File Path
     * 
     * @return string $this->filePath
     */
    public function getFilePath(): string
    {
        return $this->filePath;
    }

    /**
     * Set File Path
     * 
     * @param string $filePath
     * @return void
     */
    public function setFilePath(string $filePath): void
    {
        $this->filePath = $filePath;
    }


    /**
     * Return File Name
     * 
     * @return string $this->fileName
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * Set File Name
     * 
     * @param string $fileName
     * @return void
     */
    public function setFileName(string $fileName): void
    {
        $this->fileName = $fileName;
    }


    /**
     * Return File Size
     * 
     * @return string $this->fileSize
     */
    public function getFileSize(): int
    {
        return $this->fileSize;
    }

    /**
     * Set File Size
     * 
     * @param int $fileSize
     * @return void
     */
    public function setFileSize(int $fileSize): void
    {
        $this->fileSize = $fileSize;
    }


    /**
     * Return Mime Type
     * 
     * @return string $this->mimeType
     */
    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    /**
     * Return Mime Type Extension
     * 
     * @return string ext from $this->mimeType
     */
    public function getMimeExt(): string
    {
        // Get Extension From Mime Type
        if(!empty($this->mimeType)) {
            $mimes = explode('/', $this->mimeType);
            return end($mimes);
        }
        return '';
    }

    /**
     * Set MimeType
     * 
     * @param string $mimeType
     * @return void
     */
    public function setMimeType(string $mimeType): void
    {
        $this->mimeType = $mimeType;
    }


    /**
     * Return File Contents
     * 
     * @return string $this->fileContents
     */
    public function getContents(): string
    {
        return $this->contents;
    }

    /**
     * Set File Path
     * 
     * @param string $contents
     * @return void
     */
    public function setContents(string $contents): void
    {
        $this->contents = $contents;
    }
}