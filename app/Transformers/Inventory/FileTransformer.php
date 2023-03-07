<?php

declare(strict_types=1);

namespace App\Transformers\Inventory;

use App\Models\Inventory\File;

class FileTransformer extends MediaFileTransformer
{
    public function transform(File $file): array
    {
        $metadata = $this->getTitleMedata($file->title);

        return [
            'file_id' => $file->id,
            'title' => $metadata['title'],
            'hidden' => $metadata['hidden'],
            'mime_type' => $file->type,
            'url' => $this->getBaseUrl().$file->path,
            'type' => $file->type
        ];
    }

    /**
     * Currently the tile files are being composed by: 1) is or not a hidden file 2) title itself, so this method
     * will parse that format and returning the metadata.
     *
     * @param  string  $title
     * @return array{title: string, hidden: bool}
     */
    private function getTitleMedata(string $title): array
    {
        $metadata = [
            'hidden' => false,
            'title' => $title
        ];

        $titleParts = explode('hidden-', $title);

        if (count($titleParts) > 1) {
            $metadata['hidden'] = true;
            $metadata['title'] = $titleParts[1];
        }

        return $metadata;
    }
}
