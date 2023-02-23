<?php

namespace App\Services\User;

use Illuminate\Http\UploadedFile;

interface DealerLogoServiceInterface
{
    public function upload(int $dealerId, UploadedFile $logo): string;
}
