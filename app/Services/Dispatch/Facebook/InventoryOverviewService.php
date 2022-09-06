<?php

namespace App\Services\Dispatch\Facebook;

use App\Models\Marketing\Facebook\InventoryOverview;
use App\Services\Export\Marketplace\InventoryOverviewCsvExporter;
use Exception;
use Illuminate\Support\Facades\Storage;

class InventoryOverviewService implements InventoryOverviewServiceInterface
{
    public function export(int $id, string $fileName): string
    {
        $exporter = new InventoryOverviewCsvExporter(Storage::disk('marketplaceExports'), $fileName);

        $exporter->createFile()
            ->setHeaders($exporter->getHeaders())
            ->setLineMapper(static function (\stdClass $part) use ($exporter): array {
                return $exporter->getLineMapper($part);
            })
            ->onProgressIncrement(function (): bool {
                return true;
            })
            ->setQuery(InventoryOverview::getAllByIntegrationId($id));
        try {
            $exporter->export();
            return Storage::disk('marketplaceExports')->url($fileName);
        } catch (Exception $exception) {
            // Return it to the client
            // Report somewhere???
            return '';
        }
    }
}