<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Http\Requests\Request;
use App\Services\User\DealerLocationServiceInterface;
use Propaganistas\LaravelPhone\PhoneNumber;

class SaveDealerLocationRequest extends Request
{
    use DealerLocationRequestTrait;

    protected function getRules(): array
    {
        return [
            'dealer_id' => 'integer|min:1|required_without:id|exists:dealer,dealer_id',
            'name' => $this->validLocationName(),
            'contact' => 'required_without:id|string|min:1,max:255',
            'website' => 'nullable|string|min:0,max:255',
            'email' => 'nullable|valid_location_email|string|min:0,max:255',
            'address' => 'required_without:id|string|min:3,max:255|regex:/^[^&]+$/',
            'city' => 'required_without:id|string|min:2,max:255',
            'county' => 'required_without:id|string|min:2,max:255',
            'region' => 'required_without:id|string|min:2,max:255',
            'country' => 'required_without:id|string|min:2,max:255|in:USA,CA',
            'postalcode' => 'required_without:id|string|exists:geolocation,zip',
            'fax' => 'nullable|string|min:1,max:20',
            'phone' => 'required_without:id|string|max:20|phone:' . $this->phoneNumberCountry(),
            'is_default' => 'checkbox|in:0,1',
            'sms' => 'checkbox|in:0,1',
            'sms_phone' => 'nullable|string|max:20|required_if:sms,1|phone:' . $this->phoneNumberCountry(),
            'permanent_phone' => 'checkbox|in:0,1|required_if:sms,==,1',
            'show_on_website_locations' => 'checkbox|in:0,1',
            'county_issued' => 'nullable|min:0,max:50',
            'state_issued' => 'nullable|min:0,max:50',
            'dealer_license_no' => 'nullable|min:0,max:20',
            'federal_id' => 'nullable|min:0,max:50',
            'pac_amount' => 'nullable|numeric',
            'pac_type' => 'in:percent,amount',
            'location_id' => 'nullable|string|max:255',
            // coordinates
            'coordinates_updated' => 'checkbox|in:0,1',
            'latitude' => 'required_without:id|numeric|min:-90,max=90',
            'longitude' => 'required_without:id|numeric|min:-180,max=180',
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
            'tax_calculator_id' => 'tax_calculator_valid:' . $this->getDealerId(),
            'is_shipping_taxed' => 'checkbox|in:0,1',
            'use_local_tax' => 'checkbox|in:0,1',
            'is_env_fee_taxed' => 'checkbox|in:0,1',
            // sales tax items
            'sales_tax_items' => 'nullable|array',
            'sales_tax_items.*.entity_type_id' => $this->validTaxCatoegory(),
            'sales_tax_items.*.item_type' => 'required_with:sales_tax_items|in:state,county,city,district1,district2,district3,district4,dmv,registration',
            'sales_tax_items.*.tax_pct' => 'nullable|numeric|min:0',
            'sales_tax_items.*.tax_cap' => 'nullable|numeric|min:0',
            'sales_tax_items.*.standard' => 'checkbox|in:0,1',
            'sales_tax_items.*.out_of_state_reciprocal' => 'checkbox|in:0,1',
            'sales_tax_items.*.out_of_state_non_reciprocal' => 'checkbox|in:0,1',
            // sales_tax_item_column_titles
            'sales_tax_item_column_titles' => 'nullable|array',
            'sales_tax_item_column_titles.*.standard' => 'nullable|min:0,max:50',
            'sales_tax_item_column_titles.*.tax_exempt' => 'nullable|min:0,max:50',
            'sales_tax_item_column_titles.*.out_of_state_reciprocal' => 'nullable|min:0,max:50',
            'sales_tax_item_column_titles.*.out_of_state_non_reciprocal' => 'nullable|min:0,max:50',
            // fees
            'fees' => 'nullable|array',
            'fees.*.title' => 'required_with:fees|min:1,max:50',
            'fees.*.fee_type' => 'required_with:fees|min:1,max:50',
            'fees.*.amount' => ['required_with:fees','numeric','min:0','regex:/^(?:0|[1-9][0-9]*)(?:\.[0-9]{1,2})?$/'],
            'fees.*.cost_amount' => 'required_if:fees.*.fee_charged_type,combined|numeric|min:0',
            // 'fees.*.cost_handler' => 'required_without:id|in:set_default_cost,set_amount',
            'fees.*.is_additional' => 'checkbox|in:0,1',
            'fees.*.is_state_taxed' => 'checkbox|in:0,1',
            'fees.*.is_county_taxed' => 'checkbox|in:0,1',
            'fees.*.is_local_taxed' => 'checkbox|in:0,1',
            'fees.*.visibility' => 'required_with:fees|in:hidden,visible,visible_locked,visible_pos,visible_locked_pos',
            'fees.*.accounting_class' => 'required_with:fees|in:Adt Default Fees,Taxes & Fees Group 1,Taxes & Fees Group 2,Taxes & Fees Group 3',
            'fees.*.fee_charged_type' => 'nullable|in:income,liability,combined'
        ];
    }

    private function validLocationName(): string
    {
        return sprintf(
            'required_without:id|string|min:3,max:255|unique_dealer_location_name:%s,%s',
            $this->getDealerId(),
            $this->getId()
        );
    }

    private function validTaxCatoegory(): string
    {
        return sprintf(
            'required_with:sales_tax_items|integer|in:%s',
            implode(',', array_keys(DealerLocationServiceInterface::AVAILABLE_TAX_CATEGORIES))
        );
    }

    private function phoneNumberCountry()
    {
        return $this->country === 'USA' ? 'US' : $this->country;
    }

    /**
     * Handle a passed validation attempt.
     *
     * @return void
     */
    protected function passedValidation()
    {
        if ($this->filled('sms_phone')) {
            $this->merge([
                'sms_phone' => PhoneNumber::make($this->sms_phone, $this->phoneNumberCountry())->formatE164()
            ]);
        }
    }
}
