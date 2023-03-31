<?php

namespace App\Transformers\Integration;

use App\Models\Integration\Collector\Collector;
use League\Fractal\TransformerAbstract;

/**
 * Class CollectorTransformer
 * @package App\Transformers\Integration
 */
class CollectorTransformer extends TransformerAbstract
{
    /**
     * @param Collector $collector
     * @return array
     */
    public function transform(Collector $collector)
    {
        return [
            'id' => $collector->id,
            'dealer_id' => $collector->dealer_id,
            'dealer_location_id' => $collector->dealer_location_id,
            'process_name' => $collector->process_name,
            'ftp_host' => $collector->ftp_host,
            'ftp_path' => $collector->ftp_path,
            'ftp_login' => $collector->ftp_login,
            'ftp_password' => $collector->ftp_password,
            'file_format' => $collector->file_format,
            'path_to_data' => $collector->path_to_data,
            'create_items' => $collector->create_items,
            'update_items' => $collector->update_items,
            'archive_items' => $collector->archive_items,
            'length_format' => $collector->length_format,
            'width_format' => $collector->width_format,
            'height_format' => $collector->height_format,
            'show_on_rvtrader' => $collector->show_on_rvtrader,
            'title_format' => $collector->title_format,
            'import_prices' => $collector->import_prices,
            'import_description' => $collector->import_description,
            'images_delimiter' => $collector->images_delimiter,
            'primary_image_field' => $collector->primary_image_field,
            'overridable_fields' => $collector->overridable_fields,
            'skip_categories' => $collector->skip_categories,
            'skip_locations' => $collector->skip_locations,
            'only_types' => $collector->only_types,
            'path_to_fields_to_description' => $collector->path_to_fields_to_description,
            'fields_to_description' => $collector->fields_to_description,
            'use_secondary_image' => $collector->use_secondary_image,
            'append_floorplan_image' => $collector->append_floorplan_image,
            'update_images' => $collector->update_images,
            'update_files' => $collector->update_files,
            'import_with_showroom_category' => $collector->import_with_showroom_category,
            'unarchive_sold_items' => $collector->unarchive_sold_items,
            'active' => $collector->active,
            'cdk_password' => $collector->cdk_password,
            'cdk_username' => $collector->cdk_username,
            'cdk_dealer_cmfs' => $collector->cdk_dealer_cmfs,
            'ids_token' => $collector->ids_token,
            'ids_default_location' => $collector->ids_default_location,
            'use_factory_mapping' => $collector->use_factory_mapping,
            'is_mfg_brand_mapping_enabled' => $collector->is_mfg_brand_mapping_enabled,
            'xml_url' => $collector->xml_url,
            'csv_url' => $collector->csv_url,
            'motility_username' => $collector->motility_username,
            'motility_password' => $collector->motility_password,
            'motility_account_no' => $collector->motility_account_no,
            'motility_integration_id' => $collector->motility_integration_id,
            'zero_msrp_on_used' => $collector->zero_msrp,
            'specifications' => $collector->specifications,
            'linebreak_characters' => $collector->linebreak_characters,
            'local_image_directory_address' => $collector->local_image_directory_address,
            'use_latest_ftp_file_only' => $collector->use_latest_ftp_file_only,
            'spincar_active' => $collector->spincar_active,
            'spincar_spincar_id' => $collector->spincar_spincar_id,
            'spincar_filenames' => $collector->spincar_filenames,
            'api_url' => $collector->api_url,
            'api_key_name' => $collector->api_key_name,
            'api_key_value' => $collector->api_key_value,
            'api_params' => $collector->api_params,
            'api_max_records' => $collector->api_max_records,
            'api_pagination' => $collector->api_pagination,
            'ignore_manually_added_units' => $collector->ignore_manually_added_units,
            'is_bdv_enabled' => $collector->is_bdv_enabled,
            'show_on_auction123' => $collector->show_on_auction123,
            'video_source_fields' => $collector->video_source_fields,
            'override_all' => $collector->override_all,
            'override_images' => $collector->override_images,
            'override_video' => $collector->override_video,
            'override_prices' => $collector->override_prices,
            'override_attributes' => $collector->override_attributes,
            'override_descriptions' => $collector->override_descriptions,
            'last_run' => $collector->last_run,
            'scheduled_for' => $collector->scheduled_for,
            'use_partial_update' => $collector->use_partial_update,
            'last_full_run' => $collector->last_full_run,
            'days_till_full_run' => $collector->days_till_full_run,
            'mark_sold_manually_added_items' => $collector->mark_sold_manually_added_items,
            'not_save_unmapped_on_factory_units' => $collector->not_save_unmapped_on_factory_units,
            'conditional_title_format' => $collector->conditional_title_format,
            'use_brands_for_factory_mapping' => $collector->use_brands_for_factory_mapping,
            'check_images_for_bdv_matching' => $collector->check_images_for_bdv_matching,
        ];
    }
}
