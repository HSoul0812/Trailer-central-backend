<?php

namespace App\Services\File;

use App\Services\File\DTOs\FileDto;
use Illuminate\Support\Collection;

/**
 * Interface FileInterface
 * @package App\Services\File
 */
interface FileServiceInterface
{
    /**
     * @param string $url
     * @param string $title
     * @param int|null $dealerId
     * @param int|null $identifier
     * @param array $params
     * @return FileDto|null
     */
    public function upload(string $url, string $title, ?int $dealerId = null, ?int $identifier = null, array $params = []): ?FileDto;

    /**
     * @param array $files
     * @param int|null $dealerId
     * @return Collection<FileDto>|null
     */
    public function bulkUpload(array $files, ?int $dealerId = null): ?Collection;

    /**
     * @param array $data
     * @return FileDto
     */
    public function uploadLocal(array $data): FileDto;
}
