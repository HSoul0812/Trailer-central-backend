<?php

namespace App\Transformers\File;

use App\Services\File\DTOs\FileDto;
use League\Fractal\TransformerAbstract;

/**
 * Class FileTransformer
 * @package App\Transformers\File
 */
class FileTransformer extends TransformerAbstract
{
    /**
     * @param FileDto $fileDto
     * @return array
     */
    public function transform(FileDto $fileDto): array
    {
        return [
            'url' => $fileDto->getUrl(),
        ];
    }
}
