<?php

declare(strict_types=1);

namespace App\Services\Export\Marketplace;

use App\Services\Export\FilesystemCsvExporter as GenericFilesystemCsvExporter;
use stdClass;

class InventoryOverviewCsvExporter extends GenericFilesystemCsvExporter
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
            'Type ID' => $object->type_id,
            'Type' => $object->type,
            'Dealer ID' => $object->dealer_id,
            'Dealer Location' => $object->location,
            'Facebook User' => $object->fb_username,
            'Inventory ID' => $object->inventory_id,
            'Inventory Name' => $object->title,
            'Overview Name' => $object->name,
            'Created At' => $object->created_at
        ];
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return [
            'Type ID',
            'Type',
            'Dealer ID',
            'Dealer Location',
            'Facebook User',
            'Inventory ID',
            'Inventory Name',
            'Overview Name',
            'Created At'
        ];
    }
}
