<?php

namespace App\Models\Bulk\Parts;

use App\Models\Parts\Part;
use Illuminate\Database\Eloquent\Model;

/**
 * Class BulkDownload
 *
 * Represents a parts bulk download job
 *
 * @package App\Models\Bulk\Parts
 * @property int $dealer_id the Dealer_id
 * @property string $status status if the csv file if still building or completed
 * @property int $progress csv build progress
 * @property string $token the token/ticket returned by the request csv file api
 * @property string $export_file location of the finished file
 * @property string $result resulting messages if any. e.g. error messages
 */
class BulkDownload extends Model
{

    /**
     * new and has not started assembling
     */
    const STATUS_NEW = 'new';

    /**
     * started assembly of csv file
     */
    const STATUS_PROCESSING = 'processing';

    /**
     * csv file created successfully
     */
    const STATUS_COMPLETED = 'complete';

    /**
     * csv file was not created
     */
    const STATUS_ERROR = 'error';

    protected $table = 'parts_bulk_download';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'dealer_id',
        'status',
        'token',
        'export_file',
        'result',
    ];

    /**
     * Maps a `Part` data to respective CSV columns
     * @param Part|null $part The part to map. Set to null if only used to get the header names
     * @return array
     */
    public function lineMapper(Part $part = null)
    {
        // an empty object is needed so it does not throw an error
        if ($part === null) {
            $part = new Part();
        }
        return [
            'Vendor' => $part->vendor ? $part->vendor->name: '',
            'Brand' => $part->brand ? $part->brand->name: '',
            'Type' => $part->type? $part->type->name: '',
            'Category' => $part->category? $part->category->name: '',
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
            'Image' => !empty($part->images)? implode("\n", $part->images->all()): '',
            'Video Embed Code' => $part->video_embed_code,
            'Qty' => $part->total_qty
        ];
    }

}

/**
 * @OA\Schema(
 *     schema="parts/bulkdownload",
 *     type="object",
 *     description="Represents a parts bulk download file job",
 *     properties={
 *         @OA\Property(property="dealer_id"),
 *         @OA\Property(property="status"),
 *         @OA\Property(property="token"),
 *         @OA\Property(property="expoert_file"),
 *         @OA\Property(property="result"),
 *     }
 * )
 */

