<?php

namespace App\Services\Integration\Common\DTOs;

use App\Traits\WithConstructor;
use App\Traits\WithGetter;
use Illuminate\Http\UploadedFile;
use Webklex\PHPIMAP\Attachment;

/**
 * Class AttachmentFile
 * 
 * @package App\Services\Integration\Common\DTOs
 */
class AttachmentFile
{
    use WithConstructor, WithGetter;

    const IMAGE_TYPES = [
        'gif',
        'png',
        'jpeg',
        'jpg'
    ];

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
     * @var string
     */
    private $attachmentDir;


    /**
     * Initialize From Laravel UploadedFile
     * 
     * @param UploadedFile $file
     * @return AttachmentFile
     */
    public static function getFromUploadedFile(UploadedFile $file) {
        // Get Attachment
        $attachment = new self();

        // Set Temp Name
        $attachment->setTmpName($file->getPathname());

        // Set File Name
        $attachment->setFileName($file->getClientOriginalName());

        // Set Mime Type
        $attachment->setMimeType($file->getMimeType());

        // Set Size
        $attachment->setFileSize($file->getSize());

        // Return File Attachment
        return $attachment;
    }

    /**
     * Initialize From Existing Remote Filename
     * 
     * @param string $file
     * @return AttachmentFile
     */
    public static function getFromRemoteFile(string $file) {
        // Get File Name
        $parts = explode("/", $file);
        $filename = end($parts);
        $ext = explode(".", $filename);

        // Get Mime From Extension
        $ext[1] = !empty($ext[1]) ? $ext[1] : 'jpeg';
        if(in_array($ext[1], self::IMAGE_TYPES)) {
            $mime = 'image/' . $ext[1];
        } else {
            $mime = 'text/' . $ext[1];
        }

        // Get Headers if Possible
        $attachment = new self();
        $headers = $attachment->getFileHeaders($file);
        $mime = !empty($headers['Content-Type']) ? $headers['Content-Type'] : $mime;
        $size = !empty($headers['Content-Length']) ? $headers['Content-Length'] : 0;

        // Create Attachment File
        $attachment->setFilePath($file);
        $attachment->setFileName($filename);
        $attachment->setMimeType($mime);
        $attachment->setFileSize($size);
        return $attachment;
    }

    /**
     * Get AttachmentFile By Webklex IMAP Attachment
     * 
     * @param Attachment $attachment
     * @return AttachmentFile
     */
    public static function getByImapAttachment(Attachment $attachment): AttachmentFile {
        // Save Attachment to Directory
        $attachment->save(self::getAttachmentDir(), urlencode($attachment->getName()));

        // Return Attachment File
        return new self([
            'tmp_name' => self::getAttachmentDir() . $attachment->getName(),
            'file_name' => $attachment->getName(),
            'mime_type' => $attachment->getMimeType(),
            'file_size' => $attachment->get('size')
        ]);
    }


    /**
     * Return Temp File Name
     * 
     * @return string $this->tmpName
     */
    public function getTmpName(): string
    {
        return $this->tmpName ?? '';
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
        return $this->filePath ?? '';
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
        return $this->fileName ?? '';
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
     * @return int $this->fileSize
     */
    public function getFileSize(): int
    {
        return $this->fileSize ?? 0;
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
        // Contents Exists?
        if(!empty($this->contents)) {
            return $this->contents;
        }

        // Return Temp File Contents if Exists
        if(!empty($this->tmpName) && file_exists($this->tmpName)) {
            return file_get_contents($this->tmpName);
        }

        // Return Remote File Contents if Exists
        if(!empty($this->filePath) && file_get_contents($this->filePath) !== FALSE) {
            return file_get_contents($this->filePath);
        }

        // Return Empty String
        return '';
    }

    /**
     * Return Base64 Encoded Contents
     * 
     * @return string base64_encode($this->getContents())
     */
    public function getContentsEncoded(): string
    {
        // Get Encoded Contents
        return base64_encode($this->getContents());
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



    /**
     * Get Array Mapped Headers
     * 
     * @param string $file
     * @return array{string: string, etc...}
     */
    private function getFileHeaders(string $file): array {
        // Get Headers From Filename
        $headers = get_headers($file);

        // Start Headers
        $result = [];
        if(!empty($headers)) {
            // Map Headers
            foreach($headers as $header) {
                // Split By Colon
                $break = explode(":", $header);

                // Get Key
                $key = trim($break[0]);

                // Get Value
                $value = !empty($break[1]) ? trim($break[1]) : '';

                // Map
                if(!empty($value)) {
                    $result[$key] = $value;
                }
            }
        }

        // Return Result
        return $result;
    }


    /**
     * Return Email Attachment Params
     * 
     * @param string $messageId
     * @return array{message_id: string,
     *               filename: string,
     *               original_filename: string}
     */
    public function getParams(string $messageId): array
    {
        // Return Params
        return [
            'message_id' => $messageId,
            'filename' => $this->filePath,
            'original_filename' => $this->fileName
        ];
    }


    /**
     * Get Attachments Directory
     * 
     * @return string
     */
    public static function getAttachmentDir(): string {
        return config('mail.attachments.dir');
    }
}