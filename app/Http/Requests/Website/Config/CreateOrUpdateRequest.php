<?php


namespace App\Http\Requests\Website\Config;

use App\Http\Requests\Request;
use Illuminate\Validation\Rule;

class CreateOrUpdateRequest extends Request
{
    protected function getRules(): array
    {
        //@todo this should validate that `website_id` belongs to the dealer (then legacy www should provide an access token)
        return [
            'home/bargain_listings/title' => 'string',
            'home/bargain_listings/count' => 'integer',
            'inventory/home_latest_count' => 'integer',
            'inventory/make-offer-button' => 'boolean',
            'inventory/default_show'      => 'integer',
            'inventory/default_order'     => 'integer',
            'inventory/global_keyword_filters' => 'in:yes,no',
            'inventory/filters/hide_show_more' => 'boolean',
            'inventory/filters/visibility_brand' => 'boolean',
            'inventory/status_order' => 'string',
            'inventory/truncate_description' => 'integer',
            'inventory/print_logo' => 'string',
            'inventory/filters/enable_auto_scroll' => 'boolean',
            'inventory/duration_before_auto_archiving' => 'integer',
            'inventory/include_archived_inventory' => 'boolean',
            'inventory/show_sold_unit_in_search' => 'boolean',
            'inventory/show_stock_overlay_on_stock_photos' => 'boolean',
            'inventory/filters_refresh_page_option' => 'in:update_button,check_filter',
            'inventory/website_sidebar_filters_order' => 'in:countDesc,countAsc,nameDesc,nameAsc',
            'inventory/show_packages_on_inventory_item_page' => 'boolean',
            'inventory/additional_description' => 'string',
            'inventory/times_viewed' => 'in:show,do_not_show'
        ];
    }
}
