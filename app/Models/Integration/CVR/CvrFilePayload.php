<?php

declare(strict_types=1);

namespace App\Models\Integration\CVR;

use App\Models\Common\MonitoredJobPayload;
use File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * @property-read string $document filepath
 */
class CvrFilePayload extends MonitoredJobPayload
{
    /**
     * @var string
     */
    protected $document;

    public function asArray(): array
    {
        return ['document' => $this->document];
    }

    /**
     * @param UploadedFile|string $file
     */
    public function setDocument($file): void
    {
        $this->document = $file;

        if ($file instanceof UploadedFile) {

            $this->document = $this->generateFileNameFromUploadFile($file);

            Storage::disk('tmp')->put($this->document, file_get_contents($file->getRealPath()));
        }
    }

    /**
     * @param UploadedFile $file
     * @return string a unique filename
     */
    private function generateFileNameFromUploadFile(UploadedFile $file): string
    {
        $uniqueFileName = uniqid('cvr-' . date('Ymd'), true);
        $extension = File::extension($file->getClientOriginalName());

        return sprintf('%s.%s', str_replace('.', '-', $uniqueFileName), $extension);
    }
}
