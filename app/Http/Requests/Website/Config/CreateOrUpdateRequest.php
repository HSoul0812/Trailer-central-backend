<?php

declare(strict_types=1);

namespace App\Http\Requests\Website\Config;

use App\Http\Requests\Request;
use App\Models\Website\Config\WebsiteConfig;

class CreateOrUpdateRequest extends Request
{
    private const REGEX_IMAGE_URL = 'regex:/(http(s?):)([\/|.|\w|\s|-])*\.(?:jpg|gif|png)/';
    private const YES_NO = 'in:1,0';
    private const SHOWING_RANGE = 'in:show,do_not_show';

    protected function getRules(): array
    {
        //@todo this should validate that `website_id` belongs to the dealer
        // (then legacy www should provide an access token)
        return [
            'general/tagline' => 'string',
            'general/copyright' => 'string',
            'general/address' => 'string',
            'general/header_content' => 'string',
            'general/title_suffix' => 'string',
            'tracking/googleanalytics/id' => 'string',
            'general/favicon' => ['url', self::REGEX_IMAGE_URL],
            'general/default_meta_keywords' => 'string',
            'general/default_meta_description' => 'string',
            'inventory/home_latest_count' => ['integer', 'min:0'],
            'inventory/default_order' => ['integer', 'min:1', 'max:12'],
            'inventory/default_show' => ['integer', 'in:15,30,60'],
            'general/indicate_trailer_central' => ['integer', self::YES_NO],
            'general/mobile/enabled' => ['integer', self::YES_NO],
            'general/breadcrumbs/home_label' => 'string',
            'inventory/filters/has_range' => ['integer', self::YES_NO],
            'inventory/filters/use_cookie_zip' => ['integer', self::YES_NO],
            'inventory/truncate_description' => 'integer',
            'home/bargain_listings/count' => 'integer',
            'home/bargain_listings/title' => 'string',
            'home/latest_arrivals_title' => 'string',
            'inventory/status_order' => 'string',
            'form-success-action/general' => 'string',
            'form-success-action/inventory' => 'string',
            'call-to-action/when' => ['string', 'in:0,once,n-page-views'],
            'call-to-action/type' => ['string', 'in:newsletter,Simple Newsletter'],
            'call-to-action/when/n-page-views' => 'integer',
            'call-to-action/image/image-url' => 'string',
            'call-to-action/image/link-url' => 'string',
            'call-to-action/image/title' => 'string',
            'call-to-action/image/size' => 'string',
            'call-to-action/snippet/title' => 'string',
            'call-to-action/snippet/size' => 'string',
            'call-to-action/snippet/snippet-text' => 'string',
            'showroom/use_series' => ['integer', self::YES_NO],
            'inventory/filters/hide_show_more' => ['integer', self::YES_NO],
            'inventory/make-offer-button' => ['integer', self::YES_NO],
            'inventory/print_logo' => ['url', self::REGEX_IMAGE_URL],
            'inventory/global_keyword_filters' => ['string', 'in:yes,no'],
            'inventory/filters/shift-positions' => 'string',
            'contact/email' => 'string|nullable',
            'inventory/filters/accordion' => ['string', 'in:1,2'],
            'inventory/hybrid-list-default' => ['string', 'in:list,grid'],
            'inventory/filters/enable_auto_scroll' => ['integer', self::YES_NO],
            'inventory/filters/features' => ['integer', self::YES_NO],
            'general/dynamic_main_search' => ['integer', self::YES_NO],
            'inventory/include_archived_inventory' => ['integer', self::YES_NO],
            'inventory/additional_description' => 'string',
            'inventory/filters_refresh_page_option' => ['string', 'in:check_filter,update_button'],
            'inventory/show_sold_unit_in_search' => ['integer', self::YES_NO],
            'inventory/show_stock_overlay_on_stock_photos' => ['integer', self::YES_NO],
            'inventory/website_sidebar_filters_order' => ['string', 'in:countDesc,countAsc,nameDesc,nameAsc'],
            'general/item_email_from' => ['string', 'in:trailer_central,operate_beyond,trailer_trader'],
            'inventory/show_packages_on_inventory_item_page' => ['integer', self::YES_NO],
            'inventory/filters/visibility_brand' => ['integer', self::YES_NO],
            'inventory/duration_before_auto_archiving' => ['integer', 'in:0,24,48,168,336,504,672'],
            'inventory/times_viewed' => ['string', self::SHOWING_RANGE],
            'inventory/images_configuration' => ['string', self::SHOWING_RANGE],
            'inventory/compare' => ['string', self::SHOWING_RANGE],
            'call-to-action/recaptcha' => ['integer', 'in:0,1,on,off'],
            'home/custom_carousel_lists' => 'string',
            'contact-as-form/captcha_public_key' => 'string',
            'contact-as-form/captcha_secret_key' => 'string',
            'general/fbchat_plugin_code' => 'string',
            'general/favorites_export_emails' => 'string',
            'general/head_script' => 'string',
            'general/body_script' => 'string',
            'call-to-action/custom-text' => 'string',
            'website/show_your_search' => ['integer', self::YES_NO],
            'website/show_inventory_count_for_all_locations' => ['integer', self::YES_NO],
            'website/show_save_search' => ['integer', self::YES_NO],
            'inventory/include_description_on_search' => ['integer', self::YES_NO],
            'website/use_proximity_distance_selector' => ['integer', self::YES_NO],
            'inventory/sort_by_relevance' => ['integer', self::YES_NO],
            'leads/merge/enable' => ['integer', self::YES_NO],
            'contact-as-form/show_captcha' => ['integer', self::YES_NO],
            'general/user_accounts' => ['integer', self::YES_NO],
            'showroom/show_brands' => ['integer', self::YES_NO],
            'showroom/load_from_linked_dealers_in_global_filters' => ['integer', self::YES_NO],
            'showroom/brands' => 'string',
            'general/favorites_export_schedule' => ['integer', 'in:0,1,2,3'],
            'payment-calculator/term-list' => 'json',
            WebsiteConfig::PAYMENT_CALCULATOR_DURATION_KEY => 'string',
            'inventory/filters/enable_filters' => 'json'
        ];
    }
}
