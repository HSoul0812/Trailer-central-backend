<?php

namespace App\Domains\DealerExports;

use App\Models\DealerExport;
use App\Models\User\User;
use ZipArchive;
use Illuminate\Support\Facades\Storage;
use Exception;

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

        $s3ZipFilePath = 'exports/'. $this->dealer->dealer_id . '/dealer-archive.zip';
        $tmpZipFilePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $s3ZipFilePath;

        $zip = new ZipArchive();
        $zip->open($tmpZipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $files = Storage::disk('s3')->files('exports/' . $this->dealer->dealer_id);

        foreach($files as $file) {
            if ($file !== $s3ZipFilePath) {
                try {
                    $fileToUpload = Storage::disk('s3')->get($file);
                    $zip->addFromString(basename($file), $fileToUpload);
                } catch (Exception $e) {
                }

            }
        }
        $zip->setPassword(decrypt($dealerExport->zip_password, false));
        if ($zip->close()) {
            Storage::disk('s3')->put($s3ZipFilePath, $tmpZipFilePath);
            $dealerExport->update([
                'file_path' => Storage::disk('s3')->url($s3ZipFilePath),
                'status' => DealerExport::STATUS_PROCESSED,
            ]);

            unlink($tmpZipFilePath);
        }
    }
}
