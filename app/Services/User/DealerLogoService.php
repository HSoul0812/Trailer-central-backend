<?php

namespace App\Services\User;

use App\Repositories\User\DealerLogoRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DealerLogoService implements DealerLogoServiceInterface
{
    const UPLOAD_DIRECTORY = 'dealer_logos';
    const STORAGE_DISK = 's3';

    /**
     * @var DealerLogoRepositoryInterface
     */
    private $dealerLogoRepository;

    public function __construct(DealerLogoRepositoryInterface $dealerLogoRepository)
    {
        $this->dealerLogoRepository = $dealerLogoRepository;
    }

    public function upload(int $dealerId, UploadedFile $logo): string
    {
        $filename = $this->filename($dealerId, $logo->getClientOriginalExtension());
        $path = self::UPLOAD_DIRECTORY . '/' . $filename;

        $this->delete($dealerId);

        Storage::disk(self::STORAGE_DISK)->putFileAs(self::UPLOAD_DIRECTORY, $logo, $filename);
        return $path;
    }

    protected function filename(int $dealerId, string $extension): string
    {
        $timestamp = now()->getTimestamp();
        return "{$dealerId}_{$timestamp}_logo.{$extension}";
    }

    public function delete(int $dealerId): void
    {
        if ($existingLogo = optional($this->dealerLogoRepository->get($dealerId))->filename) {
            if (Storage::disk(self::STORAGE_DISK)->exists($existingLogo)) {
                Storage::disk(self::STORAGE_DISK)->delete($existingLogo);
            }
        }
    }
}
