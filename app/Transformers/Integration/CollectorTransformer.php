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
            'ids_token' => $collector->ids_token,
            'ids_default_location' => $collector->ids_default_location,
            'use_factory_mapping' => $collector->use_factory_mapping,
            'xml_url' => $collector->xml_url,
            'zero_msrp_on_used' => $collector->zero_msrp,

            'specifications' => $collector->specifications
        ];
    }
}
