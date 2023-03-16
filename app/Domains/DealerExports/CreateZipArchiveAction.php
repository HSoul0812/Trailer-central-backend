<?php

namespace App\Domains\DealerExports;

use App\Models\DealerExport;
use App\Models\User\User;
use Exception;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Filesystem;
use Log;
use ZipArchive;

/**
 * Class CreateZipArchiveAction
 *
 * @package App\Domains\DealerExports
 */
class CreateZipArchiveAction
{
    protected $dealer;
    protected $entityType;

    /**
     * @param User $dealer
     * @param string $entityType
     */
    public function __construct(User $dealer)
    {
        $this->dealer = $dealer;
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        $dealerExport = DealerExport::query()
            ->where('dealer_id', $this->dealer->dealer_id)
            ->where('entity_type', 'zip')
            ->first();

        $s3ZipFilePath = 'exports/' . $this->dealer->dealer_id . '/dealer-archive.zip';

        // We want to delete the existing archive so that it creates a new one always with the up to date data.
        if (Storage::disk('s3')->exists($s3ZipFilePath)) {
            Storage::disk('s3')->delete($s3ZipFilePath);
        }

        $localDisk = Storage::disk('local');
        $localDisk->put($s3ZipFilePath, '');
        $tmpZipFilePath = $localDisk->path($s3ZipFilePath);

        $zip = new ZipArchive;
        $zip->open($tmpZipFilePath, ZipArchive::CREATE);

        $files = Storage::disk('s3')->files('exports/' . $this->dealer->dealer_id);

        foreach ($files as $file) {
            if ($file !== $s3ZipFilePath) {
                try {
                    $fileToArchive = Storage::disk('s3')->get($file);
                    $zip->addFromString(basename($file), $fileToArchive);
                    $zip->setEncryptionName(basename($file), ZipArchive::EM_AES_256, decrypt($dealerExport->zip_password, false));
                } catch (Exception $e) {
                    Log::channel('dealer-export')->error($e->getMessage());
                }
            }
        }
        if ($zip->close()) {
            Storage::disk('s3')->put($s3ZipFilePath, file_get_contents($tmpZipFilePath));
            $dealerExport->update([
                'file_path' => Storage::disk('s3')->url($s3ZipFilePath),
                'status' => DealerExport::STATUS_PROCESSED,
            ]);

            unlink($tmpZipFilePath);
        }
    }
}
