<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Http\Requests\Request;

class SaveDealerLocationRequest extends Request
{
    /** @var int */
    public $dealer_id;

    /** @var string */
    public $include = '';

    protected function getRules(): array
    {
        return [
            'dealer_id' => 'integer|min:1|required|exists:dealer,dealer_id',
            'name' => 'required|string|min:3,max:255',
            'contact' => 'required|string|min:1,max:255',
            'website' => 'nullable|string|min:0,max:255|url',
            'email' => 'nullable|string|min:0,max:255|email',
            'address' => 'required|string|min:3,max:255|regex:/^[^&]+$/',
            'city' => 'required|string|min:2,max:255',
            'county' => 'required|string|min:2,max:255',
            'region' => 'required|string|min:2,max:255',
            'country' => 'required|string|min:2,max:255|in:US,CA,CL',
            'postalcode' => 'required|string|regex:/^\d{5}(?:[-\s]?\d{4})?$/',
            'fax' => 'required|string|min:1,max:20|regex:/^[01]?[- .]?\(?[2-9]\d{2}\)?[- .]?\d{3}[- .]?\d{4}$/',
            'phone' => 'required|string|max:20|regex:/^[01]?[- .]?\(?[2-9]\d{2}\)?[- .]?\d{3}[- .]?\d{4}$/',
            'is_default' => 'checkbox|in:0,1',
            'sms' => 'checkbox|in:0,1',
            'sms_phone' => 'nullable|string|max:20|regex:/^[01]?[- .]?\(?[2-9]\d{2}\)?[- .]?\d{3}[- .]?\d{4}$/',
            'permanent_phone' => 'checkbox|in:0,1',
            'show_on_website_locations' => 'checkbox|in:0,1',
            'sales_tax_item_column_titles' => 'nullable|json',
            'county_issued' => 'nullable|min:0,max:50',
            'state_issued' => 'nullable|min:0,max:50',
            'dealer_license_no' => 'nullable|min:0,max:20',
            'federal_id' => 'nullable|min:0,max:50',
            'pac_amount' => 'nullable|numeric',
            'pac_type' => 'in|percent,amount',
            // coordinates
            'coordinates_updated' => 'checkbox|in:0,1',
            'latitude' => 'nullable|numeric|min:-90,max=90',
            'longitude' => 'nullable|numeric|min:-180,max=180',
            // taxes
            'is_default_for_invoice' => 'checkbox|in:0,1',
            'sales_tax_id' => 'nullable|min:0,max:50',
            'labor_tax_type' => 'nullable|in:not_tax,always_tax,only_tax_with_parts_or_unit',
            'tax_before_trade' => 'checkbox|in:0,1',
            'taxed_on_total_of' => 'checkbox|in:0,1',
            'shop_supply_basis' => 'nullable|in:parts_and_labor,parts,labor,flat',
            'shop_supply_pct' => 'nullable|numeric|min:0',
            'shop_supply_cap' => 'nullable|numeric|min:0',
            'env_fee_basis' => 'nullable|in:parts_and_labor,parts,labor,flat',
            'env_fee_pct' => 'nullable|numeric|min:0',
            'env_fee_cap' => 'nullable|numeric|min:0',
            'is_sublet_taxed' => 'checkbox|in:0,1',
            'is_shop_supplies_taxed' => 'checkbox|in:0,1',
            'is_parts_on_service_taxed' => 'checkbox|in:0,1',
            'is_labor_on_service_taxed' => 'checkbox|in:0,1',
            'tax_calculator_id' => 'required|exists:dms_tax_calculators,id,dealer_id,' . $this->input('dealer_id'),
            'is_shipping_taxed' => 'checkbox|in:0,1',
            'include' => 'in:fees',
            'use_local_tax' => 'checkbox|in:0,1',
            'is_env_fee_taxed' => 'checkbox|in:0,1',
            // sales tax items
            'sales_tax_items' => 'nullable|array',
            'sales_tax_items.*.entity_type_id' => 'required_with:sales_tax_items|integer',
            'sales_tax_items.*.item_type' => 'required_with:sales_tax_items|in:state,county,city,district1,district2,district3,district4,dmv,registration',
            'sales_tax_items.*.tax_pct' => 'nullable|numeric|min:0',
            'sales_tax_items.*.tax_cap' => 'nullable|numeric|min:0',
            'sales_tax_items.*.standard' => 'checkbox|in:0,1',
            'sales_tax_items.*.out_of_state_reciprocal' => 'checkbox|in:0,1',
            'sales_tax_items.*.out_of_state_non_reciprocal' => 'checkbox|in:0,1',
            // fees
            'fees' => 'nullable|array',
            'fees.*.title' => 'required_with:fees|min:1,max:50',
            'fees.*.fee_type' => 'required_with:fees|min:1,max:50',
            'fees.*.amount' => 'required_with:fees|numeric|min:0',
            'fees.*.cost_amount' => 'nullable|numeric|min:0',
            'fees.*.is_additional' => 'checkbox|in:0,1',
            'fees.*.is_state_taxed' => 'checkbox|in:0,1',
            'fees.*.is_county_taxed' => 'checkbox|in:0,1',
            'fees.*.is_local_taxed' => 'checkbox|in:0,1',
            'fees.*.visibility' => 'required_with:fees|in:hidden,visible,visible_locked,visible_pos,visible_locked_pos',
            'fees.*.accounting_class' => 'required_with:fees|in:Adt Default Fees,Taxes & Fees Group 1,Taxes & Fees Group 2,Taxes & Fees Group 3',
            'fees.*.fee_charged_type' => 'required_with:fees|in:income,liability,combined'
        ];
    }
}
