<?php

namespace App\Models\Integration\Collector;

use App\Models\User\DealerLocation;
use App\Models\User\User;
use App\Utilities\JsonApi\Filterable;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Collector
 * @package App\Models\Integration\Collector
 *
 * @property int $id
 * @property int $dealer_id
 * @property int $dealer_location_id
 * @property string $process_name
 * @property string $ftp_host
 * @property string $ftp_path
 * @property string $ftp_login
 * @property string $ftp_password
 * @property boolean $active
 * @property string $file_format
 * @property string $path_to_data
 * @property boolean $create_items
 * @property string $update_items
 * @property string $archive_items
 * @property string $length_format
 * @property string $width_format
 * @property string $height_format
 * @property bool $show_on_rvtrader
 * @property string $title_format
 * @property bool $import_prices
 * @property bool $import_description
 * @property string $images_delimiter
 * @property string $overridable_fields
 * @property string $path_to_fields_to_description
 * @property string $fields_to_description
 * @property bool $use_secondary_image
 * @property bool $append_floorplan_image
 * @property bool $update_images
 * @property bool $update_files
 * @property bool $import_with_showroom_category
 * @property bool $unarchive_sold_items
 * @property string $cdk_password
 * @property string $cdk_username
 * @property bool $use_factory_mapping
 * @property string $skip_categories
 * @property string $skip_locations
 */
class Collector extends Model  implements Filterable
{
    public const FILE_FORMATS = [
        self::FILE_FORMAT_XML,
        self::FILE_FORMAT_CSV,
        self::FILE_FORMAT_CDK,
        self::FILE_FORMAT_IDS,
        self::FILE_FORMAT_XML_URL
    ];

    public const FILE_FORMAT_CDK = 'cdk';
    public const FILE_FORMAT_XML = 'xml';
    public const FILE_FORMAT_CSV = 'csv';
    public const FILE_FORMAT_IDS = 'ids';
    public const FILE_FORMAT_XML_URL = 'xml_url';
    
    public const MSRP_ZEROED_OUT_ON_USED = 1;
    public const MSRP_NOT_ZEROED_OUT_ON_USED = 0;

    public const MEASURE_FORMATS = [
        'Feet' => 'feet',
        'Inches' => 'inches',
        'Feet Inches String (e.g "10\' 22\'")' => 'feet_inches_string'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'collector';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fillable = [
        'dealer_location_id',
        'dealer_id',
        'process_name',
        'ftp_host',
        'ftp_path',
        'ftp_login',
        'ftp_password',
        'file_format',
        'path_to_data',
        'create_items',
        'update_items',
        'archive_items',
        'length_format',
        'width_format',
        'height_format',
        'active',
        'show_on_rvtrader',
        'title_format',
        'import_prices',
        'import_description',
        'images_delimiter',
        'overridable_fields',
        'path_to_fields_to_description',
        'fields_to_description',
        'use_secondary_image',
        'append_floorplan_image',
        'update_images',
        'update_files',
        'import_with_showroom_category',
        'unarchive_sold_items',
        'cdk_password',
        'cdk_username',
        'use_factory_mapping',
        'skip_categories',
        'skip_locations',
        'zero_msrp'
    ];

    public function dealers()
    {
        return $this->belongsTo(User::class, 'dealer_id', 'dealer_id');
    }

    public function dealerLocation()
    {
        return $this->belongsTo(DealerLocation::class, 'dealer_location_id', 'dealer_location_id');
    }

    public function jsonApiFilterableColumns(): ?array
    {
        return ['*'];
    }
}
