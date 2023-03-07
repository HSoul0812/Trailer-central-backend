<?php

declare(strict_types=1);

namespace App\Http\Requests\Bulk\Inventory;

use App\Http\Requests\Inventory\GetInventoryRequest;
use App\Services\Export\FilesystemPdfExporter;
use Illuminate\Validation\Rule;

/**
 * @property string $output
 * @property string $token
 * @property boolean $wait
 * @property string $orientation
 */
class CreateBulkDownloadRequest extends GetInventoryRequest
{
    public const OUTPUT_PDF = 'pdf';

    private const AVAILABLE_OUTPUTS = [
        self::OUTPUT_PDF
    ];

    private const AVAILABLE_ORIENTATIONS = [
        FilesystemPdfExporter::ORIENTATION_LANDSCAPE,
        FilesystemPdfExporter::ORIENTATION_PORTRAIT
    ];

    protected function getRules(): array
    {
        return array_merge(parent::getRules(), [
                'dealer_id' => 'required|integer',
                'token' => 'uuid',
                'wait' => 'boolean',
                'orientation' => Rule::in(self::AVAILABLE_ORIENTATIONS),
                'output' => Rule::in(self::AVAILABLE_OUTPUTS) // we could move the csv exporter here
            ]
        );
    }

    public function wait(): bool
    {
        return (bool)$this->wait;
    }

    public function output(): string
    {
        return $this->output ?: self::OUTPUT_PDF;
    }

    public function orientation(): string
    {
        return $this->orientation ?: FilesystemPdfExporter::ORIENTATION_PORTRAIT;
    }

    public function filters(): array
    {
        return collect($this->all())
            ->except(['token', 'wait', 'output', 'orientation'])
            ->toArray();
    }
}
