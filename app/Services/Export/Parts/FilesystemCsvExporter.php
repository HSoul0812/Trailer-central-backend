<?php

declare(strict_types=1);

namespace App\Services\Export\Parts;

use App\Models\Parts\Part;
use App\Services\Export\FilesystemCsvExporter as GenericFilesystemCsvExporter;
use League\Csv\Writer;

/**
 * General purpose export CSV to a `Filesystem` object from query
 */
class FilesystemCsvExporter extends GenericFilesystemCsvExporter
{
    /**
     * Crete a fileHandle where a temp csv for parts will be written to
     *
     * @return self
     */
    public function createFile(): self
    {
        $this->tmpFileName = env('APP_TMP_DIR', '/tmp') . '/part-csv-' . date('Y-m-d') . '-' . uniqid() . '.csv';
        $this->tmpFileHandle = fopen($this->tmpFileName, 'w+');

        // make a temp file use a league csv writer; fileHandle is called previously
        // TODO see if a temp file can be skipped and data can be streamed directly to Storage::put()
        $this->csvWriter = Writer::createFromStream($this->tmpFileHandle);

        return $this;
    }

    /**
     * Maps a `Part` data to respective CSV columns
     *
     * @param Part $part
     * @return array
     */
    public function getLineMapper($part): array
    {
        return [
            'Vendor' => $part->vendor ? $part->vendor->name : '',
            'Brand' => $part->brand ? $part->brand->name : '',
            'Type' => $part->type ? $part->type->name : '',
            'Category' => $part->category ? $part->category->name : '',
            'Subcategory' => $part->subcategory,
            'Title' => $part->title,
            'SKU' => $part->sku,
            'Price' => $part->price,
            'Dealer Cost' => $part->dealer_cost,
            'MSRP' => $part->msrp,
            'Weight' => $part->weight,
            'Weight Rating' => $part->weight_rating,
            'Description' => $part->description,
            'Show on website' => $part->show_on_website,
            'Image' => !empty($part->images) ? implode("\n", $part->images->all()) : '',
            'Video Embed Code' => $part->video_embed_code,
            'Qty' => $part->total_qty
        ];
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return [
            'Vendor',
            'Brand',
            'Type',
            'Category',
            'Subcategory',
            'Title',
            'SKU',
            'Price',
            'Dealer Cost',
            'MSRP',
            'Weight',
            'Weight Rating',
            'Description',
            'Show on website',
            'Image',
            'Video Embed Code',
            'Qty'
        ];
    }
}
