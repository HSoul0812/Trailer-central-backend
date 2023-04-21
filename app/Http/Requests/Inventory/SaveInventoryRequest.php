<?php

namespace App\Http\Requests\Inventory;

use App\Domains\QuickBooks\Constraints\DocNumConstraint;
use App\Http\Requests\Request;
use App\Transformers\TransformerInterface;

/**
 * Class CreateInventoryRequest
 *
 * @package App\Http\Requests\Inventory
 *
 * @property int $dealer_id
 * @property int $entity_type_id
 */
class SaveInventoryRequest extends Request
{
    protected function getRules(): array
    {
        return [
            'entity_type_id' => 'required_without_all:entity_type|integer',
            'dealer_id' => 'required_without_all:dealer_identifier|integer|exists:App\Models\User\User,dealer_id',
            'dealer_location_id' => 'required_without_all:dealer_location_identifier|integer|exists:App\Models\User\DealerLocation,dealer_location_id',
            'active' => 'boolean|nullable',
            'title' => 'required|max:255',
            'stock' => 'string|max:50',
            'manufacturer' => 'inventory_mfg_name_valid|nullable',
            'brand' => 'inventory_brand_valid:'.$this->entity_type_id.'|nullable',
            'model' => 'string|max:255|nullable',
            'qb_item_category_id' => 'integer|nullable',
            'description' => 'string|nullable|max:32765',
            'description_html' => 'string|nullable|max:32765',
            'status' => 'integer|nullable',
            'status_id' => 'integer|nullable',
            'availability' => 'string|nullable',
            'is_consignment' => 'boolean|nullable',
            'category' => 'inventory_cat_valid',
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
            'year' => 'integer',
            'chassis_year' => 'integer|nullable',
            'condition' => 'in:new,used,remfg|nullable',
            'length' => 'numeric|nullable',
            'width' => 'numeric|nullable',
            'height' => 'numeric|nullable',
            'weight' => 'numeric|nullable',
            'gvwr' => 'numeric|nullable',
            'axle_capacity' => 'numeric|nullable',
            'cost_of_unit' => 'numeric|nullable',
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
            'tt_payment_expiration_date' => 'date|nullable',
            'overlay_enabled' => 'in:0,1,2|nullable',
            'overlay_is_locked' => 'checkbox|nullable',
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
            'show_on_auction123' => 'boolean|nullable',
            'show_on_rvt' => 'boolean|nullable',

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
            'new_images.*.primary' => 'checkbox|nullable',
            'new_images.*.is_default' => 'checkbox|nullable',
            'new_images.*.secondary' => 'checkbox|nullable',
            'new_images.*.is_secondary' => 'checkbox|nullable',
            'new_images.*.was_manually_added' => 'checkbox|nullable',

            'new_files' => 'array|nullable',
            'new_files.*.title' => 'string|nullable',
            'new_files.*.url' => 'string|required',
            'new_files.*.position' => 'integer|nullable',
            'new_files.*.is_manual' => 'checkbox|nullable',

            'hidden_files' => 'array|nullable',
            'hidden_files.*.title' => 'string|nullable',
            'hidden_files.*.url' => 'string|required',
            'hidden_files.*.position' => 'integer|nullable',
            'hidden_files.*.is_manual' => 'checkbox|nullable',

            'add_bill' => 'boolean|nullable',
            'b_id' => 'int|exists:qb_bills|nullable',
            'b_vendorId' => 'int|nullable',
            'b_status' => 'string|in:due,paid|nullable',
            'b_docNum' => 'string|nullable|max:' . DocNumConstraint::MAX_LENGTH,
            'b_receivedDate' => 'date|nullable',
            'b_dueDate' => 'date|nullable',
            'b_memo' => 'string|nullable',
            'b_isFloorPlan' => 'bool|nullable',

            'craigslist' => 'array|nullable',
            'craigslist.default_image' => 'array|nullable',
            'craigslist.default_image.new' => 'checkbox|nullable',
            'craigslist.default_image.url' => 'string|nullable',

            'attributes' => 'array|nullable',
            'attributes.*' => 'array',
            'attributes.*.attribute_id' => 'required|int|exists:App\Models\Inventory\Attribute,attribute_id',
            'attribute.*.value' => 'string|nullable',

            'features' => 'array|nullable',
            'features.*.feature_list_id' => 'int|exists:App\Models\Inventory\InventoryFeatureList,feature_list_id',
            'features.*.value' => 'string',

            'clapps' => 'array|nullable',
            'clapps.*' => 'string',
        ];
    }

    /**
     * @var TransformerInterface
     */
    private $transformer;

    /**
     * {@inheritDoc}
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function all($keys = null)
    {
        return $this->transformer->transform(parent::all($keys));
    }

    /**
     * @param TransformerInterface $transformer
     */
    public function setTransformer(TransformerInterface $transformer): void
    {
        $this->transformer = $transformer;
    }
}
