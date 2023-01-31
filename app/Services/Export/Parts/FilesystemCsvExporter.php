<?php

declare(strict_types=1);

namespace App\Services\Export\Parts;

use App\Services\Export\FilesystemCsvExporter as GenericFilesystemCsvExporter;
use stdClass;

/**
 * General purpose export CSV to a `Filesystem` object from query
 */
class FilesystemCsvExporter extends GenericFilesystemCsvExporter
{
    /**
     * Maps a `Part` data to respective CSV columns
     *
     * @param stdClass $part
     * @return array
     */
    public function getLineMapper($part): array
    {
        $addedLines = [];
        foreach(explode(',', $part->bins) as $bin) {
            $id_bin = explode(';', $bin);
            if($part->qty_values !== null) {
                foreach(explode(',', $part->qty_values) as $qty) {
                    $id_qty = explode(';', $qty);
                    if($id_qty[0] === $id_bin[0]) {
                        $addedLines[$id_bin[1]] = $id_qty[1];
                    }
                }
            } else {
                $addedLines[$id_bin[1]] = 0;
            }
        }
        $addedLines['Part ID'] = $part->id;

        return array_merge([
            'SKU' => $part->sku,
            'Subcategory' => $part->subcategory,
            'Title' => $part->title,
            'Price' => $part->price,
            'Dealer Cost' => $part->dealer_cost,
            'Vendor' => $part->vendor_name,
            'Brand' => $part->brand_name,
            'Type' => $part->type_name,
            'Category' => $part->category_name,
            'MSRP' => $part->msrp,
            'Weight' => $part->weight,
            'Weight Rating' => $part->weight_rating,
            'Description' => $part->description,
            'Show on website' => $part->show_on_website,
            'Image' => $part->images,
            'Stock Minimum' => $part->stock_min,
            'Stock Maximum' => $part->stock_max,
            'Video Embed Code' => $part->video_embed_code,
            'Alternative Part Number' => $part->alternative_part_number,
            'Shipping Fee' => $part->shipping_fee,
            'Is Active' => $part->is_active,
            'Is Taxable' => $part->is_taxable,
            'Qty' => $part->total_qty,
        ], $addedLines);
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return [
            'SKU',
            'Subcategory',
            'Title',
            'Price',
            'Dealer Cost',
            'Vendor',
            'Brand',
            'Type',
            'Category',
            'MSRP',
            'Weight',
            'Weight Rating',
            'Description',
            'Show on website',
            'Image',
            'Stock Minimum',
            'Stock Maximum',
            'Video Embed Code',
            'Alternative Part Number',
            'Shipping Fee',
            'Is Active',
            'Is Taxable',
            'Qty',
        ];
    }
}
