<?php

namespace App\Domains\DealerExports;

use App\Models\DealerExport;
use App\Models\User\User;
use ZipArchive;
use Illuminate\Support\Facades\Storage;
use Exception;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\File;

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

        $zipFilePath = 'exports/'. $this->dealer->dealer_id . '/dealer-archive.zip';

        $zip = new ZipArchive();
        Storage::disk('s3')->put($zipFilePath, '');
        dump($zip->open(Storage::disk('s3')->url($zipFilePath), ZipArchive::CREATE | ZipArchive::OVERWRITE));

        $files = Storage::disk('s3')->files('exports/' . $this->dealer->dealer_id);

        foreach($files as $file) {
            if ($file !== $zipFilePath) {
                try {
                    $fileToUpload = Storage::disk('s3')->get($file);
                    $zip->addFromString(basename($file), $fileToUpload);
                    dump($zip->numFiles);
                } catch (Exception $e) {
                    dump($e);
                }

            }
        }
        $zip->setPassword(decrypt($dealerExport->zip_password, false));
        // dd($zip->count());
        if ($zip->close()) {
            // Storage::disk('s3')->put($zipFilePath, File::get(public_path($zipFilePath)));

            $dealerExport->update([
                'file_path' => Storage::disk('s3')->url($zipFilePath),
            ]);
        }
    }
}
