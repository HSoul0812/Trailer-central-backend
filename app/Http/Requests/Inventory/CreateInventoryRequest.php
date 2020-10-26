<?php

namespace App\Http\Requests\Inventory;

use App\Http\Requests\Request;
use App\Transformers\Inventory\SaveInventoryTransformer;

/**
 * Class CreateInventoryRequest
 * @package App\Http\Requests\Inventory
 */
class CreateInventoryRequest extends Request
{
    protected $rules = [
        'entity_type_id' => 'required_without_all:entity_type|integer',
        'dealer_id' => 'required_without_all:dealer_identifier|integer|exists:App\Models\User\User,dealer_id',
        'dealer_location_id' => 'required_without_all:dealer_location_identifier|integer|exists:App\Models\User\DealerLocation,dealer_location_id',
        'active' => 'boolean|nullable',
        'title' => 'required|max:255',
        'stock' => 'string|max:50|nullable',
        'manufacturer' => 'string|max:255|nullable',
        'brand' => 'string|max:255|nullable',
        'model' => 'string|max:255|nullable',
        'qb_item_category_id' => 'integer|nullable',
        'description' => 'string|nullable',
        'description_html' => 'string|nullable',
        'status' => 'integer|nullable',
        'availability' => 'string|nullable',
        'is_consignment' => 'boolean|nullable',
        'category' => 'string|max:255|nullable',
        'video_embed_code' => 'nullable',
        'vin' => 'string|max:17|nullable',
        'geolocation' => 'string',
        'msrp_min' => 'numeric|nullable',
        'msrp' => 'numeric|nullable',
        'price' => 'numeric|nullable',
        'sales_price' => 'numeric|nullable',
        'use_website_price' => 'boolean|nullable',
        'website_price' => 'numeric|nullable',
        'dealer_price' => 'numeric|nullable',
        'monthly_payment' => 'numeric|nullable',
        'year' => 'integer|min:1900|max:2050',
        'condition' => 'in:new,used,remfg|nullable',
        'length' => 'numeric|nullable',
        'width' => 'numeric|nullable',
        'height' => 'numeric|nullable',
        'weight' => 'numeric|nullable',
        'gvwr' => 'numeric|nullable',
        'axle_capacity' => 'numeric|nullable',
        'cost_of_unit' => 'string|max:255|nullable',
        'true_cost' => 'numeric|nullable',
        'cost_of_shipping' => 'string|max:255|nullable',
        'cost_of_prep' => 'string|max:255|nullable',
        'total_of_cost' => 'string|max:255|nullable',
        'pac_amount' => 'numeric|nullable',
        'pac_type' => 'in:percent,amount|nullable',
        'minimum_selling_price' => 'string|max:255|nullable',
        'notes' => 'string|nullable',
        'show_on_ksl' => 'boolean|nullable',
        'show_on_racingjunk' => 'boolean|nullable',
        'show_on_website' => 'boolean|nullable',
        'overlay_enabled' => 'in:0,1,2|nullable',
        'is_special' => 'boolean|nullable',
        'is_featured' => 'boolean|nullable',
        'latitude' => 'numeric|nullable',
        'longitude' => 'numeric|nullable',
        'is_archived' => 'boolean|nullable',
        'archived_at' => 'string|nullable',
        'broken_video_embed_code' => 'boolean|nullable',
        'showroom_id' => 'integer|nullable',
        'coordinates_updated' => 'integer|nullable',
        'payload_capacity' => 'numeric|nullable',
        'height_display_mode' => 'in:inches,feet|nullable',
        'width_display_mode' => 'in:inches,feet|nullable',
        'length_display_mode' => 'in:inches,feet|nullable',
        'width_inches' => 'numeric|nullable',
        'height_inches' => 'numeric|nullable',
        'length_inches' => 'numeric|nullable',
        'show_on_rvtrader' => 'boolean|nullable',
        'chosen_overlay' => 'string|max:255|nullable',
        'fp_committed' => 'date|nullable',
        'fp_vendor' => 'integer|nullable',
        'fp_balance' => 'numeric|nullable',
        'fp_paid' => 'boolean|nullable',
        'fp_interest_paid' => 'numeric|nullable',
        'l_holder' => 'string|max:255|nullable',
        'l_attn' => 'string|max:255|nullable',
        'l_name_on_account' => 'string|max:255|nullable',
        'l_address' => 'string|max:255|nullable',
        'l_account' => 'string|max:255|nullable',
        'l_city' => 'string|max:255|nullable',
        'l_state' => 'string|max:255|nullable',
        'l_zip_code' => 'string|max:255|nullable',
        'l_payoff' => 'numeric|nullable',
        'l_phone' => 'string|max:255|nullable',
        'l_paid' => 'boolean|nullable',
        'l_fax' => 'string|max:255|nullable',
        'bill_id' => 'integer|nullable',
        'send_to_quickbooks' => 'boolean|nullable',
        'is_floorplan_bill' => 'boolean|nullable',
        'integration_item_hash' => 'string|max:32|nullable',
        'integration_images_hash' => 'string|max:32|nullable',
        'non_serialized' => 'boolean|nullable',
        'hidden_price' => 'numeric|nullable',
        'utc_integration_updated_at' => 'date',
        'has_stock_images' => 'boolean|nullable',

        'dealer_identifier' => 'required_without_all:dealer_id|integer|exists:App\Models\User\User,dealer_id',
        'entity_type' => 'required_without_all:entity_type_id|integer',
        'dealer_location_identifier' => 'required_without_all:dealer_location_id|integer|exists:App\Models\User\DealerLocation,dealer_location_id',

        'length_second' => 'numeric|nullable',
        'length_inches_second' => 'numeric|nullable',
        'width_second' => 'numeric|nullable',
        'width_second_inches' => 'numeric|nullable',
        'height_second' => 'numeric|nullable',
        'height_second_inches' => 'numeric|nullable',

        'new_images' => 'array|nullable',
        'new_images.*.url' => 'string|required',
        'new_images.*.position' => 'integer|nullable',
        'new_images.*.primary' => 'boolean|nullable',
        'new_images.*.is_default' => 'boolean|nullable',
        'new_images.*.secondary' => 'boolean|nullable',
        'new_images.*.is_secondary' => 'boolean|nullable',
        'new_images.*.removed' => 'boolean|nullable',

        'new_files' => 'array|nullable',
        'new_files.*.title' => 'string|nullable',
        'new_files.*.path' => 'string|nullable',
        'new_files.*.position' => 'integer|nullable',

        'add_bill' => 'boolean|nullable',
    ];

    /**
     * {@inheritDoc}
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function all($keys = null)
    {
        /** @var SaveInventoryTransformer $transformer */
        $transformer = app()->make(SaveInventoryTransformer::class);

        return $transformer->transform(parent::all());
    }
}
