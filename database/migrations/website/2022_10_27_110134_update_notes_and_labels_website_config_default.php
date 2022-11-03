<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateNotesAndLabelsWebsiteConfigDefault extends Migration
{
    private const NEW_VALUES_INDEXED_BY_KEY = [
        'home/latest_arrivals_title' => ['note' => 'Title for the latest arrivals carrousel.'],
        'call-to-action/when' => ['note' => 'Whether to show a CTA modal in the middle of the screen and what frequency to show it to the user.'],
        'call-to-action/type' => ['note' => 'What kind of CTA to show. Currently only newsletter is supported.'],
        'call-to-action/image/image-url' => ['note' => 'CTA image to be used.'],
        'call-to-action/image/link-url' => ['note' => 'Where the CTA link will point to.'],
        'call-to-action/image/title' => ['note' => 'Sets the title tag for the CTA image.'],
        'call-to-action/image/size' => ['note' => 'Max size for the CTA image in the following format 750x650 or any other values.'],
        'showroom/use_series' => ['note' => null],
        'inventory/filters/enable_filters' => ['note' => null],
        'general/dynamic_main_search' => ['note' => 'This controls the website header search bars.  If enabled it will show the user quick suggestions as they type the keyword they want to search for.'],
        'inventory/include_archived_inventory' => ['note' => "Determine if Archived Inventory remains as part of the sitemap.  Archived units have Contact CTA's disabled.  (may not work for highly custom sites)"],
        'inventory/filters_refresh_page_option' => ['note' => 'Choose to either refresh the page after each filter is selected or display an Apply button to refresh page and load results.'],
        'inventory/show_sold_unit_in_search' => ['note' => 'Allows Sold units to be found using the Keyword or Stock number search.'],
        'inventory/show_stock_overlay_on_stock_photos' => ['note' => 'Determine whether Overlays are automatically applied to stock photos added by incoming inventory feeds.'],
        'inventory/website_sidebar_filters_order' => ['note' => 'Set the sort order of the filters within each filter group in the left hand filter bar.'],
        'home/custom_carousel_lists' => ['note' => 'This accepts JSON and expects attributes indicating the title as well as filters for the carousel.'],
        'inventory/show_packages_on_inventory_item_page' => ['note' => 'If packages are built, choose whether or not to include them in the inventory list page results.'],
        'inventory/filters/visibility_brand' => ['note' => 'Determines if all brands are always visible in filter category or if only brands with 1 or more units are displayed.'],
        'showroom/show_brands' => ['note' => 'Select brands that will be displayed in the showroom.'],
        'showroom/brands' => ['note' => 'Enables Brand display on the showroom page.  Only valid on Marine and RV units.'],
        'inventory/duration_before_auto_archiving' => ['note' => 'Determine how long an inventory item will remain in my inventory before being automatically archived when marked sold.'],
        'general/user_accounts' => ['note' => 'Allows users to create an account on the dealer website.'],
        'inventory/times_viewed' => ['label' => 'Display Views on Details Page', 'note' => "When enabled, count of views is displayed on each inventory item's details page."],
        'inventory/images_configuration' => ['label' => 'Hide Inventory without Images', 'note' => 'Determines if inventory with an image count of 0 is or is not included in the inventory list pages.'],
        'inventory/compare' => ['label' => 'Compare Function', 'note' => 'When enabled, users can select multiple units from the inventory list page and be taken to a compare page which places the units side by side.'],
        'leads/merge/enabled' => ['note' => 'When checked, new leads arriving will merge with existing lead if any two of the following match - Full Name, Phone, Email.  Status will be changed to New Inquiry.  (disable for any dealer using lead exports)'],
        'payment-calculator/term-list' => ['note' => 'Select which terms to allow a user to select from on the Payment Calculator that appears on the Inventory Details Page for each unit.'],
        'inventory/include_description_on_search' => ['note' => 'When enabled all words in the description will be included in the keyword search criteria.  When disabled, only the Title will be searched.'],
        'general/favorites_export_schedule' => ['note' => 'When Favorites functionality is enabled, this determines the schedule in which the dealer will receive the favorite exports email.'],
        'website/show_inventory_count_for_all_locations' => ['note' => 'When activated, if design implements Inventory by Type on Homepage, the count of inventory will be total count of each type for All Locations regardless of which location is currently selected.'],
        'inventory/sort_by_relevance' => ['note' => 'When disabled, keyword search results will follow Status Sort Order and Default Sort Order configurations.  When enabled, results are sorted by how relevant they are to the searched keyword.  Count of results should be the same in either case.'],
        'website/show_save_search' => ['label' => 'Save Search Function', 'note' => 'When enabled, users can save set of applied filters to view later.  Requires Show Your Search to be enabled and user account functionality to be enabled.  Users must first create an account to save a search.'],
        'general/head_script' => ['note' => "Head scripts to be added to between the head tags. Dealers can't see and have no control over this."],
        'call-to-action/recaptcha' => ['note' => 'Enables reCaptcha on the Pop Up form when enabled.'],
        'website/use_proximity_distance_selector' => ['note' => 'Only available for select dealers.'],
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        foreach (self::NEW_VALUES_INDEXED_BY_KEY as $key => $config) {
            DB::table('website_config_default')
                ->where('key', $key)
                ->update($config);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        $configRollback = [
            'inventory/times_viewed' => ['label' => 'It shows how many times your units have been viewed by visitors.', 'note' => null],
            'inventory/images_configuration' => ['label' => 'Select whether to show inventory without images or not.', 'note' => null],
            'inventory/compare' => ['label' => 'It allows your users to compare multiple units with each other.', 'note' => null],
            'website/show_save_search' => ['label' => 'Show Save Search', 'note' => null],
        ];

        foreach (self::NEW_VALUES_INDEXED_BY_KEY as $key => $config) {
            DB::table('website_config_default')
                ->where('key', $key)
                ->update($configRollback[$key] ?? ['note' => null]);
        }
    }
}
