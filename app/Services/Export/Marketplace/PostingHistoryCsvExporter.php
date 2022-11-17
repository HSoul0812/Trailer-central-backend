<?php

declare(strict_types=1);

namespace App\Services\Export\Marketplace;

use App\Services\Export\FilesystemCsvExporter as GenericFilesystemCsvExporter;
use stdClass;
class PostingHistoryCsvExporter extends GenericFilesystemCsvExporter
{
    /**
     * Maps a `Marketplace` data to respective CSV columns
     *
     * @param stdClass $object
     * @return array
     */
    public function getLineMapper($object): array
    {
        return [
            'Record ID' => $object->record_id,
            'Type' => $object->type,
            'Status' => $object->status,
            'Dealer ID' => $object->dealer_id,
            'Dealer Location' => $object->location,
            'Facebook User' => $object->fb_username,
            'Facebook ID' => $object->facebook_id,
            'Inventory ID' => $object->inventory_id,
            'Stock' => $object->SKU,
            'Created At' => $object->created_at
        ];
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return [
            'Record ID',
            'Type',
            'Status',
            'Dealer ID',
            'Dealer Location',
            'Facebook User',
            'Facebook ID',
            'Inventory ID',
            'Stock',
            'Created At'
        ];
    }
}
