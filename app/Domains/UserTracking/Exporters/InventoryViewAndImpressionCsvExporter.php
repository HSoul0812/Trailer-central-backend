<?php

namespace App\Domains\UserTracking\Exporters;

use App\Domains\UserTracking\Actions\GetPageNameFromUrlAction;
use App\Domains\UserTracking\Types\UserTrackingEvent;
use App\Models\UserTracking;
use Carbon\Carbon;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Collection;
use Storage;

class InventoryViewAndImpressionCsvExporter
{
    const HEADERS = [
        'Dealer Id',
        'Inventory Id',
        'Domain',
        'Date & Time',
        'Displayed On',
    ];

    const PAGE_NAME_MAPPINGS = [
        GetPageNameFromUrlAction::PAGE_NAMES['TT_PLP'] => 'PLP',
        GetPageNameFromUrlAction::PAGE_NAMES['TT_PDP'] => 'PDP',
        GetPageNameFromUrlAction::PAGE_NAMES['TT_DEALER'] => 'DEALER',
    ];

    private int $chunkSize = 1000;

    private ?string $filename = null;

    private Carbon $from;

    private Carbon $to;

    private FilesystemAdapter $disk;

    /** @var resource */
    private $file;

    public function __construct()
    {
        $this->disk = Storage::disk('inventory-view-and-impression-reports');
    }

    public function export(): string
    {
        // Create a file using Laravel filesystem, so we don't
        // need to deal with creating missing folders
        $dateString = $this->from->format('Y-m-d');
        $filename = $this->filename ?: "inventory-view-and-impression-" . $dateString . '.csv';
        $this->disk->put($filename, '');

        // Open the file resource
        $filePath = $this->disk->path($filename);
        $this->file = fopen($filePath, 'w');

        // Write the CSV header
        fputcsv($this->file, self::HEADERS);

        UserTracking::query()
            ->where('created_at', '>=', $this->from)
            ->where('created_at', '<=', $this->to)
            ->whereIn('event', [UserTrackingEvent::PAGE_VIEW, UserTrackingEvent::IMPRESSION])
            ->whereIn('page_name', [
                GetPageNameFromUrlAction::PAGE_NAMES['TT_PLP'],
                GetPageNameFromUrlAction::PAGE_NAMES['TT_PDP'],
                GetPageNameFromUrlAction::PAGE_NAMES['TT_DEALER'],
            ])
            ->whereNotNull('meta')
            ->chunkById($this->chunkSize, function (Collection $userTrackings) use (&$csvData) {
                foreach ($userTrackings as $userTracking) {
                    $this->processUserTracking($userTracking);
                }
            });

        return $filePath;
    }

    /**
     * @return int
     */
    public function getChunkSize(): int
    {
        return $this->chunkSize;
    }

    /**
     * @param int $chunkSize
     * @return InventoryViewAndImpressionCsvExporter
     */
    public function setChunkSize(int $chunkSize): InventoryViewAndImpressionCsvExporter
    {
        $this->chunkSize = $chunkSize;

        return $this;
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     * @return InventoryViewAndImpressionCsvExporter
     */
    public function setFilename(string $filename): InventoryViewAndImpressionCsvExporter
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * @return Carbon
     */
    public function getFrom(): Carbon
    {
        return $this->from;
    }

    /**
     * @param Carbon $from
     * @return InventoryViewAndImpressionCsvExporter
     */
    public function setFrom(Carbon $from): InventoryViewAndImpressionCsvExporter
    {
        $this->from = $from;

        return $this;
    }

    /**
     * @return Carbon
     */
    public function getTo(): Carbon
    {
        return $this->to;
    }

    /**
     * @param Carbon $to
     * @return InventoryViewAndImpressionCsvExporter
     */
    public function setTo(Carbon $to): InventoryViewAndImpressionCsvExporter
    {
        $this->to = $to;

        return $this;
    }

    /**
     * @return FilesystemAdapter
     */
    public function getDisk(): FilesystemAdapter
    {
        return $this->disk;
    }

    /**
     * @param FilesystemAdapter $disk
     * @return InventoryViewAndImpressionCsvExporter
     */
    public function setDisk(FilesystemAdapter $disk): InventoryViewAndImpressionCsvExporter
    {
        $this->disk = $disk;

        return $this;
    }

    private function processUserTracking(UserTracking $userTracking): void
    {
        if (empty($userTracking->meta)) {
            return;
        }

        $parsed = parse_url($userTracking->url);

        $pageName = $userTracking->page_name ?? 'N/A';

        if (array_key_exists($pageName, self::PAGE_NAME_MAPPINGS)) {
            $pageName = self::PAGE_NAME_MAPPINGS[$pageName];
        }

        foreach ($userTracking->meta as $meta) {
            fputcsv($this->file, [
                data_get($meta, 'dealer_id', 'N/A'),
                data_get($meta, 'inventory_id', 'N/A'),
                $parsed['host'],
                $userTracking->created_at,
                $pageName,
            ]);
        }
    }
}
