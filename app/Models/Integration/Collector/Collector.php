<?php

namespace App\Models\Integration\Collector;

use App\Models\User\DealerLocation;
use App\Models\User\User;
use App\Utilities\JsonApi\Filterable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
 * @property string $primary_image_field
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
 * @property string $cdk_dealer_cmfs
 * @property bool $use_factory_mapping
 * @property bool is_mfg_brand_mapping_enabled
 * @property string $skip_categories
 * @property string $skip_locations
 * @property string|null $ids_token
 * @property string|null $ids_default_location
 * @property string|null $xml_url
 * @property string|null $csv_url
 * @property string|null $pipe_delimited
 * @property string|null $motility
 * @property string|null $generic_json
 * @property string|null $bish
 * @property string|null $csvs
 * @property string|null $zero_msrp
 * @property string|null $only_types
 * @property string|null $linebreak_characters
 * @property bool $use_latest_ftp_file_only
 * @property bool $spincar_active
 * @property int|null $spincar_spincar_id
 * @property string|null $spincar_filenames
 * @property string|null $api_url
 * @property string|null $api_key_name
 * @property string|null $api_key_value
 * @property string|null $api_params
 * @property string|null $api_max_records
 * @property string|null $api_pagination
 *
 * @property Collection<CollectorLog> $collectorLogs
 * @property Collection<CollectorSpecification> $specifications
 *
 * @property User $dealers
 * @property DealerLocation $dealerLocation
 * @property bool $ignore_manually_added_units
 * @property bool $is_bdv_enabled
 * @property bool $show_on_auction123
 * @property string|null $motility_username
 * @property string|null $motility_password
 * @property string|null $motility_account_no
 * @property string|null $motility_integration_id
 * @property string|null $local_image_directory_address
 * @property string|null $video_source_fields
 * @property int $override_images
 * @property int $override_all
 * @property int $override_video
 * @property int $override_prices
 * @property int $override_attributes
 * @property int $override_descriptions
 * @property string|null $third_party_provider
 * @property \DateTime|null $last_run
 * @property \DateTime|null $scheduled_for
 * @property bool $use_partial_update
 * @property int $days_till_full_run
 * @property \DateTime|null $last_full_run
 * @property bool $remove_unmapped_on_factory_units
 * @property string $conditional_title_format
 * @property bool $use_brands_for_factory_mapping
 * @property bool $check_images_for_bdv_matching
 * @property bool $mark_sold_manually_added_items
 * @property bool $not_save_unmapped_on_factory_units
 * @property int|null $factory_mapping_filter_year_from
 * @property int|null $factory_mapping_filter_skip_units
 *
 */
class Collector extends Model implements Filterable
{
    public const FILE_FORMATS = [
        self::FILE_FORMAT_XML,
        self::FILE_FORMAT_CSV,
        self::FILE_FORMAT_CDK,
        self::FILE_FORMAT_CDK_MULTIPLE,
        self::FILE_FORMAT_IDS,
        self::FILE_FORMAT_XML_URL,
        self::FILE_FORMAT_PIPE_DELIMITED,
        self::FILE_FORMAT_MOTILITY,
        self::FILE_FORMAT_JSON,
        self::FILE_FORMAT_BISH,
        self::FILE_FORMAT_CSV_SIMPLE,
        self::FILE_FORMAT_CSV_URL,
    ];

    public const FILE_FORMAT_CDK = 'cdk';
    public const FILE_FORMAT_CDK_MULTIPLE = 'cdk_multiple';
    public const FILE_FORMAT_XML = 'xml';
    public const FILE_FORMAT_CSV = 'csv';
    public const FILE_FORMAT_IDS = 'ids';
    public const FILE_FORMAT_XML_URL = 'xml_url';
    public const FILE_FORMAT_PIPE_DELIMITED = 'pipe_delimited';
    public const FILE_FORMAT_MOTILITY = 'motility';
    public const FILE_FORMAT_JSON = 'json';
    public const FILE_FORMAT_BISH = 'bish';
    public const FILE_FORMAT_CSV_SIMPLE = 'csvs';
    public const FILE_FORMAT_CSV_URL = 'csv_url';

    public const MSRP_ZEROED_OUT_ON_USED = 1;
    public const MSRP_NOT_ZEROED_OUT_ON_USED = 0;

    public const OVERRIDE_NOT_SET = 0;
    public const OVERRIDE_UNLOCKED = 1;
    public const OVERRIDE_LOCKED = 2;

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
        'id',
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
        'primary_image_field',
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
        'cdk_dealer_cmfs',
        'motility_username',
        'motility_password',
        'motility_account_no',
        'motility_integration_id',
        'use_factory_mapping',
        'is_mfg_brand_mapping_enabled',
        'skip_categories',
        'skip_locations',
        'zero_msrp',
        'only_types',
        'linebreak_characters',
        'use_latest_ftp_file_only',
        'spincar_active',
        'spincar_spincar_id',
        'spincar_filenames',
        'api_url',
        'api_key_name',
        'api_key_value',
        'api_params',
        'api_max_records',
        'api_pagination',
        'ignore_manually_added_units',
        'is_bdv_enabled',
        'last_run',
        'run_errors',
        'show_on_auction123',
        'video_source_fields',
        'override_images',
        'override_all',
        'override_video',
        'override_prices',
        'override_attributes',
        'override_descriptions',
        'third_party_provider',
        'use_partial_update',
        'days_till_full_run',
        'last_full_run',
        'mark_sold_manually_added_items',
        'not_save_unmapped_on_factory_units',
        'conditional_title_format',
        'use_brands_for_factory_mapping',
        'check_for_bdv_matching',
        'factory_mapping_filter_year_from',
        'factory_mapping_filter_skip_units'
    ];

    protected $casts = [
        'last_run' => 'datetime',
        'last_full_run' => 'datetime',
        'scheduled_for' => 'datetime',
        'overridable_fields' => 'array'
    ];

    public function getOverridableFieldsListAttribute(): string
    {
        if (!is_array($this->overridable_fields)) {
            $this->overridable_fields = json_decode($this->overridable_fields, true);
        }

        $overridable_fields = array_keys(array_filter($this->overridable_fields, function ($v) {
            return $v;
        }));

        return implode(",", $overridable_fields);
    }

    public function dealers(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dealer_id', 'dealer_id');
    }

    public function dealerLocation(): BelongsTo
    {
        return $this->belongsTo(DealerLocation::class, 'dealer_location_id', 'dealer_location_id');
    }

    public function specifications(): HasMany
    {
        return $this->hasMany(CollectorSpecification::class);
    }

    public function collectorLogs(): HasMany
    {
        return $this->hasMany(CollectorLog::class);
    }

    public function jsonApiFilterableColumns(): ?array
    {
        return ['*'];
    }

    public function collectorChangeReports()
    {
        return $this->hasMany(CollectorChangeReport::class);
    }

    public function collectorAdminNotes()
    {
        return $this->hasMany(CollectorAdminNote::class);
    }
}
