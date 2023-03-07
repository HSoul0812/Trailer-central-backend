<?php

namespace App\Services\Dispatch\Facebook;

use App\Models\Marketing\Facebook\PostingHistory;
use App\Services\Export\Marketplace\PostingHistoryCsvExporter;
use Exception;
use Illuminate\Support\Facades\Storage;

class PostingHistoryService implements PostingHistoryServiceInterface
{
    /**
     * Export the integration's run history
     *
     * @param int $id
     * @param string $fileName
     * @return string
     */
    public function export(int $id, string $fileName): string
    {
        $exporter = new PostingHistoryCsvExporter(Storage::disk('marketplaceExports'), $fileName);

        $exporter->createFile()
            ->setHeaders($exporter->getHeaders())
            ->setLineMapper(static function (\stdClass $part) use ($exporter): array {
                return $exporter->getLineMapper($part);
            })
            ->onProgressIncrement(function (): bool {
                return true;
            })
            ->setQuery(PostingHistory::getAllByIntegrationIdQuery($id));
        try {
            $exporter->export();
            return Storage::disk('marketplaceExports')->url($fileName);
        } catch (Exception $exception) {
            // Return it to the client
            // Report somewhere???
            return '';
        }
    }

    /**
     * Export all the integration's run history
     *
     * @param string $fileName
     * @return string
     */
    public function exportAll(string $fileName): string
    {
        $exporter = new PostingHistoryCsvExporter(Storage::disk('marketplaceExports'), $fileName);

        $exporter->createFile()
            ->setHeaders($exporter->getHeaders())
            ->setLineMapper(static function (\stdClass $part) use ($exporter): array {
                return $exporter->getLineMapper($part);
            })
            ->onProgressIncrement(function (): bool {
                return true;
            })
            ->setQuery(PostingHistory::getAllQuery());
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