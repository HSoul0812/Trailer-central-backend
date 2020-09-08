<?php


use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateMigrations20200827 extends Seeder
{
    public function run()
    {
        DB::statement("TRUNCATE TABLE `migrations_api`");
        DB::statement("
            INSERT INTO `migrations_api` (`migration`, `batch`)
            VALUES
            ('2014_10_12_000000_create_users_table', 1),
            ('2014_10_12_100000_create_password_resets_table', 1),
            ('2019_10_15_174223_create_parts_table', 1),
            ('2019_10_15_182218_create_brands_table', 1),
            ('2019_10_15_184105_insert_brands_v1', 1),
            ('2019_10_15_185438_create_categories_table', 1),
            ('2019_10_15_185835_insert_categories', 1),
            ('2019_10_15_190549_create_types_table', 1),
            ('2019_10_15_191043_insert_part_types_v1', 1),
            ('2019_10_15_191358_create_manufacturer_table', 1),
            ('2019_10_15_191440_insert_part_manufacturers', 1),
            ('2019_10_15_193053_add_part_foreign_keys', 1),
            ('2019_10_16_000341_add_part_images_table', 1),
            ('2019_10_17_082546_create_auth_token_table', 1),
            ('2019_10_17_151019_add_user_foreign_key', 1),
            ('2019_10_17_183037_create_vehicle_specific_table', 1),
            ('2019_10_17_183739_add_vehicle_specific_id_to_parts', 1),
            ('2019_10_17_195706_make_vehicle_specific_fields_null', 1),
            ('2019_10_17_201657_add_part_id_to_vehicle_specific_remove_from_parts_v1', 1),
            ('2019_10_17_231051_add_video_embed_code_to_parts', 1),
            ('2019_10_17_231650_add_position_to_part_images', 1),
            ('2019_10_17_234921_change_weight_rating_in_parts', 1),
            ('2019_10_18_183922_create_parts_v1_indexes', 1),
            ('2019_10_18_234342_add_indexes_to_part_filters', 1),
            ('2019_10_19_152716_create_parts_cache_store_times', 1),
            ('2019_10_21_144412_create_part_bin_qty', 1),
            ('2019_10_21_173024_create_parts_bulk_upload', 1),
            ('2019_10_21_214148_add_validation_error_to_bulk_upload', 1),
            ('2019_10_21_232947_generate_access_tokens', 1),
            ('2020_02_29_105733_create_feed_table', 1),
            ('2020_03_04_193326_create_parts_bulk_download_table', 1),
            ('2020_04_02_145343_make_subcategory_nullable', 1),
            ('2020_04_14_142133_create_feed_api_uploads_table', 1),
            ('2020_04_18_010732_add_alternative_number_to_parts_table', 1),
            ('2020_04_22_114832_dms_413_parts_add_min_max', 1),
            ('2020_05_27_142545_create_website_blog', 1),
            ('2020_06_11_193102_update_homesteader_manufacturer', 1),
            ('2020_06_18_145615_create_make_towing_capacity_table', 1),
            ('2020_06_23_030446_add_quote_indexes', 1),
            ('2020_06_25_180049_add_index_to_payment', 1),
            ('2020_06_30_084053_create_vehicle_towing_capacity_table', 1),
            ('2020_07_01_180049_add_inspection_info_to_unit_sale', 1),
            ('2020_07_01_200254_create_table_dealer_refunds_items', 1),
            ('2020_07_02_151531_modify_entity_type_column_in_website_entity_table', 1),
            ('2020_07_02_183623_create_crm_text_templates_campaigns_blasts', 1),
            ('2020_07_07_135815_add_columns_to_towing_capacity_vehicles_table', 1),
            ('2020_07_08_081340_create_dealer_location_sales_tax_item_v2', 1),
            ('2020_07_08_152914_add_service_order_indexes', 1),
            ('2020_07_08_162606_column_unit_sale_fee_missing_fields', 1),
            ('2020_07_08_193620_add_is_wholesale_to_customers', 1),
            ('2020_07_09_020537_update_floorplan_payment_table', 1),
            ('2020_07_09_083248_update_quickbook_approval', 1),
            ('2020_07_09_153600_add_total_price_to_repair_order', 1),
            ('2020_07_13_122107_add_export_format_to_lead_email_table', 1),
            ('2020_07_13_150506_change_type_enum_to_dealer_incoming_mappings_table', 1),
            ('2020_07_14_234220_create_crm_lead_assign_table', 1),
            ('2020_07_15_081254_add_meta_to_pos_products', 1),
            ('2020_07_15_093928_add_quantity_to_service_item', 1),
            ('2020_07_16_081254_add_po_payment_method', 1),
            ('2020_07_16_081258_add_po_no_to_crm_pos_sales', 1),
            ('2020_07_20_144203_change_type_of_payment_method', 1),
            ('2020_07_21_081258_add_loan_and_dmv_fees', 1),
            ('2020_07_21_150127_add_auth_token_user_type', 1),
            ('2020_07_22_180613_add_crm_inventory_lead_id_index', 1),
            ('2020_07_24_020544_add_new_inventory_categories', 1),
            ('2020_07_27_021123_add_unit_sale_fee_missing_fields', 1),
            ('2020_07_27_051123_add_coxautoinc_integration', 1),
            ('2020_07_27_085229_add_new_item_type', 1),
            ('2020_07_27_180216_update_inventory_category_table', 1),
            ('2020_07_28_091119_create_purchase_order_receipt_table', 1),
            ('2020_07_28_125917_add_changed_fields_in_dashboard_field_to_inventory_table', 1),
            ('2020_07_29_125917_add_middle_name_to_dms_customers', 1),
            ('2020_07_30_135940_add_is_billed_to_po_receipt', 1),
            ('2020_08_03_143946_register_add_meta', 1),
            ('2020_08_04_143946_change_forest_river_inc_to_forest_river', 1),
            ('2020_08_05_125901_add_slider_fields_to_website_entity_table', 1),
            ('2020_08_05_143946_change_lead_email_table', 1),
            ('2020_08_06_162756_customer_add_new_fields1', 1),
            ('2020_08_06_172505_remove_order_type_in_po', 1),
            ('2020_08_09_193259_add_crm_lead_assign_indexes', 1),
            ('2020_08_09_194425_associate_pos_sale_to_invoice', 1),
            ('2020_08_10_145800_add_is_po_to_unit_sale', 1),
            ('2020_08_10_185800_add_lien_holder_state', 1),
            ('2020_08_11_220951_quote_inventory_add_notes', 1),
            ('2020_08_13_015025_remove_duplicated_po_num', 1),
            ('2020_08_13_085025_add_vinsolutions_integration', 1),
            ('2020_08_13_115025_add_fin_to_financing_company', 1),
            ('2020_08_13_125025_add_lien_license_to_unit_sale', 1),
            ('2020_08_13_192820_refunds_add_qty_returned', 1),
            ('2020_08_13_215839_quote_inventory_add_inspection', 1),
            ('2020_08_14_095656_add_filters_refresh_page_to_website_config_default_table', 1),
            ('2020_08_14_125025_add_error_result_to_quickbook_approval', 1),
            ('2020_08_14_125342_add_side_wall_height_to_eav_attribute_table', 1),
            ('2020_08_21_153748_add_sales_person_id_to_crm_interaction_table', 1),
            ('2020_08_25_152155_add_semi_trailer_pull_type_to_eav_entity_type_attribute_table', 1),
            ('2020_08_26_165209_refunds_add_register_id', 1),
            ('2020_08_26_185032_register_add_refund', 1),
            ('2018_01_01_000000_create_action_events_table', 1),
            ('2019_05_10_000000_add_fields_to_action_events_table', 1);
        ");
    }
}