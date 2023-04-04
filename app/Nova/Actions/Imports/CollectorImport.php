<?php

namespace App\Nova\Actions\Imports;

use App\Models\Integration\Collector\Collector;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

/**
 * Class CollectorImport
 * @package App\Nova\Actions\Imports
 */
class CollectorImport implements ToModel, WithStartRow
{

    /**
    * @param array $row
    *
    * @return Collector
     */
    public function model(array $row): Collector
    {
        return new Collector([
            'id' => $row[0],
            'process_name' => $row[1],
            'dealer_id' => $row[2],
            'dealer_location_id' => $row[3],
            'ftp_host' => $row[4],
            'ftp_path' => $row[5],
            'ftp_login' => $row[6],
            'ftp_password' => $row[7],
            'file_format' => $row[8],
            'path_to_data' => $row[9],
            'create_items' => $row[10],
            'update_items' => $row[11],
            'archive_items' => $row[12],
            'length_format' => $row[13],
            'width_format' => $row[14],
            'height_format' => $row[15],
            'title_format' => $row[16],
            'import_prices' => $row[17],
            'import_description' => $row[18],
            'images_delimiter' => $row[19],
            'overridable_fields' => json_encode(json_decode($row[20], true)),
            'path_to_fields_to_description' => $row[21],
            'fields_to_description' => $row[22],
            'use_secondary_image' => $row[23] ?? 0,
            'append_floorplan_image' => $row[24] ?? 0,
            'update_images' => $row[25] ?? 0,
            'update_files' => $row[26] ?? 0,
            'import_with_showroom_category' => $row[27] ?? 0,
            'unarchive_sold_items' => $row[28] ?? 0,
            'active' => $row[29] ?? 0,
            'cdk_username' => $row[30],
            'cdk_password' => $row[31],
            'ids_token' => $row[32],
            'ids_default_location' => $row[33],
            'use_factory_mapping' => $row[34] ?? 0,
            'xml_url' => $row[35],
            'skip_categories' => $row[36],
            'skip_locations' => $row[37],
            'zero_msrp' => $row[38] ?? 0,
            'linebreak_characters' => $row[39],
            'only_types' => $row[40],
            'local_image_directory_address' => $row[41],
            'motility_username' => $row[42],
            'motility_password' => $row[43],
            'motility_account_no' => $row[44],
            'motility_integration_id' => $row[45],
            'use_latest_ftp_file_only' => $row[46] ?? 0,
            'spincar_active' => $row[47] ?? 0,
            'spincar_spincar_id' => $row[48],
            'spincar_filenames' => $row[49],
            'api_url' => $row[50],
            'api_key_name' => $row[51],
            'api_key_value' => $row[52],
            'api_params' => $row[53],
            'api_max_records' => $row[54],
            'api_pagination' => $row[55],
            'ignore_manually_added_units' => $row[56] ?? 0,
            'is_bdv_enabled' => $row[57] ?? 0,
            'last_run' => $row[58],
            'run_without_errors' => $row[59] ?? 1,
            'csv_url' => $row[60],
            'show_on_rvtrader' => $row[61] ?? 0,
            'show_on_auction123' => $row[62] ?? 0,
            'scheduled_for' => $row[63],
            'video_source_fields' => $row[64],
            'is_mfg_brand_mapping_enabled' => $row[65] ?? 0,
            'cdk_dealer_cmfs' => $row[66],
            'override_all' => $row[67] ?? 0,
            'override_images' => $row[68] ?? 0,
            'override_video' => $row[69] ?? 0,
            'override_prices' => $row[70] ?? 0,
            'override_attributes' => $row[71] ?? 0,
            'override_descriptions' => $row[72] ?? 0,
            'third_party_provider' => $row[73],
            'created_at' => $row[74],
            'updated_at' => $row[75],
            'use_partial_update' => $row[73] ?? 0,
            'last_full_run' => $row[74] ?? 0,
            'days_till_full_run' => $row[75] ?? 0,
            'not_save_unmapped_on_factory_units' => $row[76] ?? 0,
            'conditional_title_format' => $row[77] ?? 0,
            'use_brands_for_factory_mapping' => $row[78] ?? 0,
            'check_images_for_bdv_matching' => $row[79] ?? 0,
            'mark_sold_manually_added_items' => $row[80] ?? 0,
            'created_at' => $row[81],
            'updated_at' => $row[82],
        ]);
    }

    /**
     * @return int
     */
    public function startRow(): int
    {
        return 2;
    }
}
