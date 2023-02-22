<?php

namespace App\Services\User;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DealerLogoService implements DealerLogoServiceInterface
{
    const UPLOAD_DIRECTORY = 'dealer_logos';

    public function upload(int $dealerId, UploadedFile $logo): string
    {
        $filename = $this->filename($dealerId, $logo->getClientOriginalExtension());
        $path = self::UPLOAD_DIRECTORY . '/' . $filename;
        
        Storage::disk('s3')->putFileAs(self::UPLOAD_DIRECTORY, $logo, $filename);
        return $path;
    }

    protected function filename(int $dealerId, string $extension): string
    {
        return "{$dealerId}_logo.{$extension}";
    }
}
