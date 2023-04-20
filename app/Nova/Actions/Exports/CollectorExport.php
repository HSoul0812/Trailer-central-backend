<?php

namespace App\Nova\Actions\Exports;

use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;

use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\LaravelNovaExcel\Actions\DownloadExcel;

/**
 * Class CollectorExport
 * @package App\Nova\Actions\Exports
 */
class CollectorExport extends DownloadExcel implements WithHeadings, WithMapping, WithStyles, WithEvents, WithStrictNullComparison
{
    /**
     * @var string
     */
    public $name = "Export Collectors";

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'Process Name',
            'Dealer ID',
            'Dealer Location ID',
            'FTP Host',
            'FTP Path',
            'FTP Login',
            'FTP Password',
            'File Format',
            'Path to Data',
            'Create Items',
            'Update Items',
            'Archive Items',
            'Length Format',
            'Width Format',
            'Height Format',
            'Title Format',
            'Import Prices',
            'Import Description',
            'Images Delimiter',
            'Overridable Fields',
            'Path to Fields to Description',
            'Fields To Description',
            'Use Secondary Image',
            'Append Floorplan Image',
            'Update Images',
            'Update Files',
            'Import With Showroom Category',
            'Unarchive Sold Items',
            'Active',
            'CDK Username',
            'CDK Password',
            'IDS Token',
            'IDS Default Location',
            'Use Factory Mapping',
            'XML Url',
            'Skip Categories',
            'Skip Locations',
            'Zero MSRP',
            'Linebreak Characters',
            'Only Types',
            'Local Image Directory Address',
            'Motility Username',
            'Motility Password',
            'Motility Account No',
            'Motility Integration ID',
            'Use Latest FTP File Only',
            'Spincar Active',
            'Spincar Spincar ID',
            'Spincar Filenames',
            'API Url',
            'API Key Name',
            'API Key Value',
            'API Params',
            'API Max Records',
            'API Pagination',
            'Ignore Manually Added Units',
            'Is BDV Enabled',
            'Last Run',
            'Run Without Errors',
            'CSV Url',
            'Show on RvTrader',
            'Show on Auction123',
            'Scheduled For',
            'Video Source Fields',
            'Is MFG Brand Mapping Enabled',
            'CDK Dealer CMFS',
            'Override All',
            'Override Images',
            'Override Video',
            'Override Prices',
            'Override Attributes',
            'Override Descriptions',
            'Third Party Provider',
            'Use Partial Update',
            'Last Full Run',
            'Days Till Full Run',
            'Don\'t save unmapped items',
            'Conditional Title Format',
            'Use brands',
            'Check for matching with existing bdv images',
            'Mark Sold Manually Added Items',
            'FV Filter Year',
            'FV Filter Skip',
            'Created At',
            'Updated At',
        ];
    }

    /**
     *
     * @param $mapping
     * @return array
     */
    public function map($mapping): array
    {
        return [
            $mapping->id,
            $mapping->process_name,
            $mapping->dealer_id,
            $mapping->dealer_location_id,
            $mapping->ftp_host,
            $mapping->ftp_path,
            $mapping->ftp_login,
            $mapping->ftp_password,
            $mapping->file_format,
            $mapping->path_to_data,
            $mapping->create_items,
            $mapping->update_items,
            $mapping->archive_items,
            $mapping->length_format,
            $mapping->width_format,
            $mapping->height_format,
            $mapping->title_format,
            $mapping->import_prices,
            $mapping->import_description,
            $mapping->images_delimiter,
            $mapping->overridable_fields,
            $mapping->path_to_fields_to_description,
            $mapping->fields_to_description,
            $mapping->use_secondary_image,
            $mapping->append_floorplan_image,
            $mapping->update_images,
            $mapping->update_files,
            $mapping->import_with_showroom_category,
            $mapping->unarchive_sold_items,
            $mapping->active,
            $mapping->cdk_username,
            $mapping->cdk_password,
            $mapping->ids_token,
            $mapping->ids_default_location,
            $mapping->use_factory_mapping,
            $mapping->xml_url,
            $mapping->skip_categories,
            $mapping->skip_locations,
            $mapping->zero_msrp,
            $mapping->linebreak_characters,
            $mapping->only_types,
            $mapping->local_image_directory_address,
            $mapping->motility_username,
            $mapping->motility_password,
            $mapping->motility_account_no,
            $mapping->motility_integration_id,
            $mapping->use_latest_ftp_file_only,
            $mapping->spincar_active,
            $mapping->spincar_spincar_id,
            $mapping->spincar_filenames,
            $mapping->api_url,
            $mapping->api_key_name,
            $mapping->api_key_value,
            $mapping->api_params,
            $mapping->api_max_records,
            $mapping->api_pagination,
            $mapping->ignore_manually_added_units,
            $mapping->is_bdv_enabled,
            $mapping->last_run,
            $mapping->run_without_errors,
            $mapping->csv_url,
            $mapping->show_on_rvtrader,
            $mapping->show_on_auction123,
            $mapping->scheduled_for,
            $mapping->video_source_fields,
            $mapping->is_mfg_brand_mapping_enabled,
            $mapping->cdk_dealer_cmfs,
            $mapping->override_all,
            $mapping->override_images,
            $mapping->override_video,
            $mapping->override_prices,
            $mapping->override_attributes,
            $mapping->override_descriptions,
            $mapping->third_party_provider,
            $mapping->use_partial_update,
            $mapping->last_full_run,
            $mapping->days_till_full_run,
            $mapping->not_save_unmapped_on_factory_units,
            $mapping->conditional_title_format,
            $mapping->use_brands_for_factory_mapping,
            $mapping->check_images_for_bdv_matching,
            $mapping->mark_sold_manually_added_items,
            $mapping->factory_mapping_filter_year_from,
            $mapping->factory_mapping_filter_skip_units,
            $mapping->created_at,
            $mapping->updated_at,
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => [
                    'bold' => true
                ]
            ],
        ];
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                foreach ($event->sheet->getColumnIterator() as $column) {
                    $event->sheet->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
                }
            },
        ];
    }
}
