<?php

namespace App\Domains\DealerExports\Quotes;

use App\Contracts\DealerExports\EntityActionExportable;
use App\Domains\DealerExports\BaseExportAction;
use App\Models\CRM\Account\InvoiceItem;
use App\Models\CRM\Dms\Quickbooks\Item;
use App\Models\CRM\Dms\ServiceOrder;
use Illuminate\Support\Facades\DB;

/**
 * Class QuotesExportAction
 *
 * @package App\Domains\DealerExports\Quotes
 */
class QuotesExportAction extends BaseExportAction implements EntityActionExportable
{
    public const ENTITY_TYPE = 'quotes';

    public function getQuery()
    {
        return DB::query()
            ->select([
                'dms_unit_sale.*',
                'qb_invoices.unit_sale_id as unit_sale_id',
                'qb_invoices.id as invoice_no',
                'qb_invoices.invoice_date as invoice_date',
                DB::raw("'Unit Sale' as invoice_type"),
                'dealer_location.name as invoice_sales_location',
                DB::raw('dealer_location.dealer_location_id as invoice_sales_location_id'),
                'dms_customer.id as buyer_id',
                'dms_customer.display_name as buyer_display_name',
                'dms_customer.address as buyer_address',
                'dms_customer.city as buyer_city',
                'dms_customer.county as buyer_county',
                'dms_customer.region as buyer_state',
                'dms_customer.postal_code as buyer_postal_code',
                'dms_customer.cell_phone as buyer_phone',
                'dms_customer.email as buyer_email',
                DB::raw("if(dms_customer.tax_exempt = 1, 'TRUE', 'FALSE') as buyer_tax_exempt"),
                'dms_customer.tax_id_number as buyer_tax_id',
                DB::raw("if(dms_customer.is_wholesale = 1, 'TRUE', 'FALSE') as wholesale_customer"),
                DB::raw("CONCAT(sales_person_1.first_name, ' ', sales_person_1.last_name) as sales_person_1"),
                DB::raw("coalesce(concat(sales_person_2.first_name, ' ', sales_person_2.last_name), '') as sales_person_2"),
                'dms_unit_sale.tax_profile as tax_profile',
                'dms_unit_sale.inventory_id as unit_id',
                'inventory.title as unit_title',
                'inventory.stock as unit_stock',
                DB::raw("coalesce(inventory.vin, '') as unit_vin"),
                'eav_entity_type.title as unit_type',
                DB::raw("coalesce(if(inventory_category.inventory_category_id is not null, inventory_category.label, inventory_category_legacy.label), '') as unit_category"),
                'inventory.year as unit_year',
                'inventory.manufacturer as unit_mfg',
                'inventory.model as unit_model',
                'inventory.gvwr as unit_gvwr',
                'inventory.brand as unit_brand',
                'unit_location.dealer_location_id as unit_location_id',
                'unit_location.name as unit_location',
                DB::raw("if(inventory.condition = 'new', 'New', if(inventory.condition = 'used', 'Used', 'Dealer')) as unit_condition"),
                'qb_invoice_items.unit_price as unit_retail_price',
                DB::raw(sprintf("
                    (
                        select abs(unit_discount_qb_invoice_items.unit_price)
                        from qb_invoice_items as unit_discount_qb_invoice_items
                        left join qb_items as unit_discount_qb_items on unit_discount_qb_items.id = unit_discount_qb_invoice_items.item_id
                        where unit_discount_qb_invoice_items.referenced_item_id = qb_invoice_item_inventories.invoice_item_id
                        and (unit_discount_qb_items.type = '%s' and unit_discount_qb_items.name = '%s')
                    ) as unit_discount
                ", Item::ITEM_TYPES['DISCOUNT'], Item::NAMES['INVENTORY_DISCOUNT'])),
                DB::raw('0 as unit_total_after_discount'),
                DB::raw('if(qb_invoice_item_inventories.inventory_id = dms_unit_sale.inventory_id, 1, 0) as unit_is_main'),
                DB::raw('0 as unit_total_after_discount_less_trade_in'),
                'dms_unit_sale.meta as unit_sale_metadata',
                DB::raw('if(qb_invoices.tax_before_trade is null, 0, qb_invoices.tax_before_trade) as unit_tax_before_trade'),
                DB::raw('0 as unit_total_tax_rate'),
                DB::raw('0 as unit_total_sales_tax_amount'),
                DB::raw('0 as unit_state_tax_rate'),
                DB::raw('0 as unit_state_tax_amount'),
                DB::raw('0 as unit_county_tax_rate'),
                DB::raw('0 as unit_county_tax_amount'),
                DB::raw('0 as unit_local_tax_rate'),
                DB::raw('0 as unit_local_tax_amount'),
                DB::raw('0 as unit_other_tax_rate'),
                DB::raw('0 as unit_other_tax_amount'),
                DB::raw('0 as unit_taxable_amount'),
                DB::raw('0 as unit_nontaxable_amount'),
                DB::raw('coalesce(inventory.cost_of_unit, 0) as unit_cost'),
                DB::raw('coalesce(inventory.cost_of_shipping, 0) as unit_cost_of_shipping'),
                DB::raw(sprintf("
                    coalesce((
                        select sum(dms_repair_order.total_price)
                        from dms_repair_order
                        where dms_repair_order.inventory_id = inventory.inventory_id AND dms_repair_order.type = '%s'
                        group by dms_repair_order.inventory_id
                    ), 0) as unit_cost_of_ros
                ", ServiceOrder::TYPE_INTERNAL)),
                DB::raw('coalesce(inventory.cost_of_prep, 0) as unit_cost_of_prep'),
                DB::raw('0 as unit_total_cost'),
                'inventory.true_cost as unit_true_cost',
                DB::raw("coalesce(qb_bills.doc_num, '') as unit_associated_bill_no"),
                DB::raw('0 as unit_total_true_cost'),
                DB::raw('coalesce(inventory.pac_amount, 0) as unit_pac_amount'),
                'inventory.pac_type as unit_pac_type',
                DB::raw('0 as unit_pac_adj'),
                DB::raw('0 as unit_cost_overhead_percent'),
                DB::raw('0 as unit_cost_plus_overhead'),
                DB::raw('coalesce(inventory.minimum_selling_price, 0) as unit_min_selling_price'),
                DB::raw("coalesce(qb_vendors.name, '') as unit_floorplan_vendor"),
                DB::raw("coalesce(if(inventory.fp_committed = '0000-00-00', '', inventory.fp_committed), '') as unit_floorplan_committed_date"),
                DB::raw('coalesce(inventory.fp_balance, 0) as unit_floorplan_balance'),
                DB::raw('
                    coalesce((
                        select sum(trade_in_trade_value_trade_in.trade_value)
                        from dms_unit_sale_trade_in_v1 as trade_in_trade_value_trade_in
                        where trade_in_trade_value_trade_in.unit_sale_id = dms_unit_sale.id
                    ), 0) as trade_in_trade_value
                '),
                DB::raw('
                    coalesce((
                        select sum(coalesce(trade_in_trade_value_trade_in.lien_payoff_amount, 0))
                        from dms_unit_sale_trade_in_v1 as trade_in_trade_value_trade_in
                        where trade_in_trade_value_trade_in.unit_sale_id = dms_unit_sale.id
                    ), 0) as trade_in_lien_payoff_amount
                '),
                DB::raw('0 as trade_in_net_trade'),
                DB::raw(sprintf("
                    coalesce((
                        select sum(qb_invoice_items.unit_price)
                        from qb_invoice_items
                        left join qb_items as additional_pricing_price_qb_items on additional_pricing_price_qb_items.id = qb_invoice_items.item_id
                        where qb_invoice_items.invoice_id = qb_invoices.id
                        and additional_pricing_price_qb_items.type = '%s'
                    ), 0) as additional_pricing_price
                ", Item::ITEM_TYPES['ADD_ON'])),
                DB::raw(sprintf("
                    coalesce((
                        select sum(additional_pricing_cost_qb_items.cost)
                        from qb_invoice_items
                        left join qb_items as additional_pricing_cost_qb_items on additional_pricing_cost_qb_items.id = qb_invoice_items.item_id
                        where qb_invoice_items.invoice_id = qb_invoices.id
                        and additional_pricing_cost_qb_items.type = '%s'
                    ), 0) as additional_pricing_cost
                ", Item::ITEM_TYPES['ADD_ON'])),
                DB::raw(sprintf("
                    coalesce((
                        select sum(qb_invoice_items.unit_price)
                        from qb_invoice_items
                        left join qb_items as additional_pricing_taxable_amount_qb_items on additional_pricing_taxable_amount_qb_items.id = qb_invoice_items.item_id
                        left join dealer_location_quote_fee as additional_pricing_taxable_amount_quote_fee on additional_pricing_taxable_amount_quote_fee.id = additional_pricing_taxable_amount_qb_items.item_primary_id
                        where qb_invoice_items.invoice_id = qb_invoices.id
                        and additional_pricing_taxable_amount_qb_items.type = '%s'
                        and (
                            additional_pricing_taxable_amount_quote_fee.is_state_taxed = 1 or
                            additional_pricing_taxable_amount_quote_fee.is_county_taxed = 1 or
                            additional_pricing_taxable_amount_quote_fee.is_local_taxed = 1
                        )
                    ), 0) as additional_pricing_taxable_amount
                ", Item::ITEM_TYPES['ADD_ON'])),
                DB::raw(sprintf("
                    coalesce((
                        select sum(qb_invoice_items.unit_price)
                        from qb_invoice_items
                        left join qb_items as additional_pricing_taxable_amount_qb_items on additional_pricing_taxable_amount_qb_items.id = qb_invoice_items.item_id
                        left join dealer_location_quote_fee as additional_pricing_taxable_amount_quote_fee on additional_pricing_taxable_amount_quote_fee.id = additional_pricing_taxable_amount_qb_items.item_primary_id
                        where qb_invoice_items.invoice_id = qb_invoices.id
                        and additional_pricing_taxable_amount_qb_items.type = '%s'
                        and (
                            additional_pricing_taxable_amount_quote_fee.is_state_taxed = 0 and
                            additional_pricing_taxable_amount_quote_fee.is_county_taxed = 0 and
                            additional_pricing_taxable_amount_quote_fee.is_local_taxed = 0
                        )
                    ), 0) as additional_pricing_nontaxable_amount
                ", Item::ITEM_TYPES['ADD_ON'])),
                DB::raw(sprintf("
                    coalesce((
                        select sum(qb_invoice_items.unit_price)
                        from qb_invoice_items
                        left join qb_items as additional_pricing_taxable_amount_qb_items on additional_pricing_taxable_amount_qb_items.id = qb_invoice_items.item_id
                        left join dealer_location_quote_fee as additional_pricing_taxable_amount_quote_fee on additional_pricing_taxable_amount_quote_fee.id = additional_pricing_taxable_amount_qb_items.item_primary_id
                        where qb_invoice_items.invoice_id = qb_invoices.id
                        and additional_pricing_taxable_amount_qb_items.type = '%s'
                        and additional_pricing_taxable_amount_quote_fee.is_state_taxed = 1
                    ), 0) as additional_pricing_state_taxable_amount
                ", Item::ITEM_TYPES['ADD_ON'])),
                DB::raw(sprintf("
                    coalesce((
                        select sum(qb_invoice_items.unit_price)
                        from qb_invoice_items
                        left join qb_items as additional_pricing_taxable_amount_qb_items on additional_pricing_taxable_amount_qb_items.id = qb_invoice_items.item_id
                        left join dealer_location_quote_fee as additional_pricing_taxable_amount_quote_fee on additional_pricing_taxable_amount_quote_fee.id = additional_pricing_taxable_amount_qb_items.item_primary_id
                        where qb_invoice_items.invoice_id = qb_invoices.id
                        and additional_pricing_taxable_amount_qb_items.type = '%s'
                        and additional_pricing_taxable_amount_quote_fee.is_county_taxed = 1
                    ), 0) as additional_pricing_county_taxable_amount
                ", Item::ITEM_TYPES['ADD_ON'])),
                DB::raw(sprintf("
                    coalesce((
                        select sum(qb_invoice_items.unit_price)
                        from qb_invoice_items
                        left join qb_items as additional_pricing_taxable_amount_qb_items on additional_pricing_taxable_amount_qb_items.id = qb_invoice_items.item_id
                        left join dealer_location_quote_fee as additional_pricing_taxable_amount_quote_fee on additional_pricing_taxable_amount_quote_fee.id = additional_pricing_taxable_amount_qb_items.item_primary_id
                        where qb_invoice_items.invoice_id = qb_invoices.id
                        and additional_pricing_taxable_amount_qb_items.type = '%s'
                        and additional_pricing_taxable_amount_quote_fee.is_local_taxed = 1
                    ), 0) as additional_pricing_local_taxable_amount
                ", Item::ITEM_TYPES['ADD_ON'])),
                DB::raw('0 as additional_pricing_state_tax'),
                DB::raw('0 as additional_pricing_county_tax'),
                DB::raw('0 as additional_pricing_local_tax'),
                DB::raw('0 as additional_pricing_total_tax_rate'),
                DB::raw(sprintf("
                    coalesce((
                        select sum(part_qb_invoice_items.qty * qb_items.cost)
                        from qb_items
                        left join qb_invoice_items as part_qb_invoice_items on part_qb_invoice_items.item_id = qb_items.id
                        left join qb_invoices as part_qb_invoices on part_qb_invoices.id = part_qb_invoice_items.invoice_id
                        where part_qb_invoices.unit_sale_id = dms_unit_sale.id
                        and qb_items.type = '%s'
                    ), 0) as part_cost
                ", 'part')),
                DB::raw(sprintf("
                    coalesce((
                        select sum(part_qb_invoice_items.qty * part_qb_invoice_items.unit_price)
                        from qb_items
                        left join qb_invoice_items as part_qb_invoice_items on part_qb_invoice_items.item_id = qb_items.id
                        left join qb_invoices as part_qb_invoices on part_qb_invoices.id = part_qb_invoice_items.invoice_id
                        where part_qb_invoices.unit_sale_id = dms_unit_sale.id
                        and qb_items.type = '%s'
                    ), 0) as part_total_price
                ", 'part')),
                DB::raw(sprintf("
                    coalesce((
                        select abs(qb_invoice_items.unit_price)
                        from qb_invoice_items
                        left join qb_invoices as add_on_qb_invoices on add_on_qb_invoices.id = qb_invoice_items.invoice_id
                        where add_on_qb_invoices.unit_sale_id = dms_unit_sale.id
                        and qb_invoice_items.description = '%s'
                    ), 0) as part_discount
                ", 'Part Discount')),
                DB::raw('0 as part_tax_rate_applied'),
                DB::raw('
                    (
                        select sum(part_total_taxable_amount_accessory.qty * part_total_taxable_amount_accessory.price)
                        from dms_unit_sale_accessory as part_total_taxable_amount_accessory
                        where qb_invoices.unit_sale_id = part_total_taxable_amount_accessory.unit_sale_id
                        and part_total_taxable_amount_accessory.taxable = 1
                    ) as part_total_taxable_amount
                '),
                DB::raw('
                    (
                        select sum(part_total_taxable_amount_accessory.qty * part_total_taxable_amount_accessory.price)
                        from dms_unit_sale_accessory as part_total_taxable_amount_accessory
                        where qb_invoices.unit_sale_id = part_total_taxable_amount_accessory.unit_sale_id
                        and part_total_taxable_amount_accessory.taxable = 0
                    ) as part_total_nontaxable_amount
                '),
                DB::raw('0 as part_total_taxable_amount_after_discount'),
                DB::raw('0 as part_state_tax_amount'),
                DB::raw('0 as part_county_tax_amount'),
                DB::raw('0 as part_local_tax_amount'),
                DB::raw('0 as total_parts_tax_amount'),
                DB::raw(sprintf("
                    coalesce((
                        select sum(qb_invoice_items.qty * qb_invoice_items.unit_price)
                        from qb_invoice_items
                        left join qb_items as labor_subtotal_qb_items on labor_subtotal_qb_items.id = qb_invoice_items.item_id
                        where qb_invoice_items.invoice_id = qb_invoices.id
                        and labor_subtotal_qb_items.type = '%s'
                    ), 0) as labor_subtotal
                ", Item::ITEM_TYPES['LABOR'])),
                DB::raw(sprintf("
                    coalesce((
                        select abs(qb_invoice_items.unit_price)
                        from qb_invoice_items
                        left join qb_items as labor_discount_qb_items on labor_discount_qb_items.id = qb_invoice_items.item_id
                        where qb_invoice_items.invoice_id = qb_invoices.id
                        and labor_discount_qb_items.name = '%s'
                    ), 0) as labor_discount
                ", Item::NAMES['LABOR_DISCOUNT'])),
                'dealer_location_sales_tax.labor_tax_type as labor_tax_type_from_location',
                DB::raw('0 as labor_total_after_discount'),
                DB::raw('0 as labor_taxable_amount'),
                DB::raw('0 as labor_nontaxable_amount'),
                DB::raw('0 as labor_tax_rate_applied'),
                DB::raw('0 as labor_state_tax_amount'),
                DB::raw('0 as labor_county_tax_amount'),
                DB::raw('0 as labor_local_tax_amount'),
                DB::raw('0 as labor_total_tax_amount'),
                DB::raw('qb_invoices.total as invoice_total'),
                DB::raw('0 as invoice_nontaxable_total'),
                DB::raw('0 as invoice_taxable_total'),
                DB::raw(sprintf("
                    coalesce((
                        select abs(qb_invoice_items.qty * qb_invoice_items.unit_price)
                        from qb_invoice_items
                        where qb_invoice_items.invoice_id = qb_invoices.id
                        and qb_invoice_items.description = '%s'
                    ), 0) as state_tax_total
                ", InvoiceItem::DESCRIPTION_DEAL_STATE_TAX)),
                DB::raw(sprintf("
                    coalesce((
                        select abs(qb_invoice_items.qty * qb_invoice_items.unit_price)
                        from qb_invoice_items
                        where qb_invoice_items.invoice_id = qb_invoices.id
                        and qb_invoice_items.description = '%s'
                    ), 0) as county_tax_total
                ", InvoiceItem::DESCRIPTION_DEAL_COUNTY_TAX)),
                DB::raw(sprintf("
                    coalesce((
                        select abs(qb_invoice_items.qty * qb_invoice_items.unit_price)
                        from qb_invoice_items
                        where qb_invoice_items.invoice_id = qb_invoices.id
                        and qb_invoice_items.description = '%s'
                    ), 0) as city_tax_total
                ", InvoiceItem::DESCRIPTION_DEAL_LOCAL_TAX)),
                DB::raw('0 as warranty_tax_total'),
                DB::raw('0 as other_taxes_total'),
                DB::raw('0 as total_invoice_tax'),
                DB::raw('dms_unit_sale.total_price as total_amount_due'),
                'qb_payment_methods.name as payment_type',
                'qb_payment.date as payment_date',
                DB::raw(sprintf("
                    (
                        select coalesce(sum(qb_payment.amount), 0) - coalesce(sum(payment_dealer_refunds.amount), 0)
                        from qb_payment
                        left join qb_invoices as payment_qb_invoices on payment_qb_invoices.id = qb_payment.invoice_id
                        left join dealer_refunds as payment_dealer_refunds on payment_dealer_refunds.tb_primary_id = qb_payment.id and payment_dealer_refunds.tb_name = '%s'
                        where payment_qb_invoices.unit_sale_id = qb_invoices.unit_sale_id
                    ) as payment_received_total_amount
                ", 'qb_payment')),
                DB::raw('0 as remaining_balance'),
            ])
            ->from('dms_unit_sale')
            ->leftJoin('qb_invoices', 'qb_invoices.unit_sale_id', '=', 'dms_unit_sale.id')
            ->leftJoin('qb_invoice_items', 'qb_invoice_items.invoice_id', '=', 'qb_invoices.id')
            ->leftJoin('qb_invoice_item_inventories', 'qb_invoice_item_inventories.invoice_item_id', '=', 'qb_invoice_items.item_id')
            ->leftJoin('dealer_location', 'dealer_location.dealer_location_id', '=', 'qb_invoices.dealer_location_id')
            ->leftJoin('dealer_location_sales_tax', 'dealer_location_sales_tax.dealer_location_id', '=', 'qb_invoices.dealer_location_id')
            ->leftJoin('dms_customer', 'dms_customer.id', '=', 'qb_invoices.customer_id')
            ->leftJoin('crm_sales_person as sales_person_1', 'sales_person_1.id', '=', 'dms_unit_sale.sales_person_id')
            ->leftJoin('crm_sales_person as sales_person_2', 'sales_person_2.id', '=', 'dms_unit_sale.sales_person1_id')
            ->leftJoin('inventory', 'inventory.inventory_id', '=', 'qb_invoice_item_inventories.inventory_id')
            ->leftJoin('eav_entity_type', 'eav_entity_type.entity_type_id', '=', 'inventory.entity_type_id')
            ->leftJoin('inventory_category', 'inventory_category.category', '=', 'inventory.category')
            ->leftJoin('inventory_category as inventory_category_legacy', 'inventory_category_legacy.legacy_category', '=', 'inventory.category')
            ->leftJoin('dealer_location as unit_location', 'unit_location.dealer_location_id', '=', 'inventory.dealer_location_id')
            ->leftJoin('qb_bills', 'qb_bills.id', '=', 'inventory.bill_id')
            ->leftJoin('qb_vendors', 'qb_vendors.id', '=', 'inventory.fp_vendor')
            ->leftJoin('dms_unit_sale_trade_in_v1', 'dms_unit_sale_trade_in_v1.unit_sale_id', '=', 'dms_unit_sale.id')
            ->leftJoin('inventory_category as trade_in_inventory_category', 'trade_in_inventory_category.inventory_category_id', '=', 'dms_unit_sale_trade_in_v1.temp_inv_category')
            ->leftJoin('eav_entity_type as trade_in_eav_entity_types', 'trade_in_eav_entity_types.entity_type_id', '=', 'trade_in_inventory_category.entity_type_id')
            ->leftJoin('manufacturers as trade_in_manufacturers', 'trade_in_manufacturers.id', '=', 'dms_unit_sale_trade_in_v1.temp_inv_mfg')
            ->leftJoin('qb_payment', 'qb_payment.invoice_id', '=', 'qb_invoices.id')
            ->leftJoin('qb_payment_methods', 'qb_payment_methods.id', '=', 'qb_payment.payment_method_id')
            ->where('qb_invoices.dealer_id', $this->dealer->dealer_id)
            ->orderBy('qb_invoices.invoice_date');
    }

    public function execute(): void
    {
        $this->setEntity(self::ENTITY_TYPE)
            ->setHeaders([
                'unit_sale_id' => 'Quote/Deal Identifier',
                'title' => 'Quote/Deal Title',
                'invoice_no' => 'Invoice No.',
                'invoice_date' => 'Invoice Date',
                'invoice_type' => 'Invoice Type',
                'invoice_sales_location_id' => 'Invoice (Sales) Location Identifier',
                'invoice_sales_location' => 'Invoice (Sales) Location',
                'buyer_id' => 'Buyer Identifier',
                'buyer_display_name' => 'Buyer Name',
                'buyer_address' => 'Buyer Address',
                'buyer_city' => 'Buyer City',
                'buyer_county' => 'Buyer County',
                'buyer_state' => 'Buyer State',
                'buyer_postal_code' => 'Buyer Zip Code',
                'buyer_phone' => 'Buyer Phone',
                'buyer_email' => 'Buyer Email',
                'buyer_tax_exempt' => 'Buyer Is Tax Exempt',
                'buyer_tax_id' => 'Buyer Tax ID',
                'wholesale_customer' => 'Wholesale Customer: True/False',
                'default_discount' => 'Default Discount',
                'billing_address' => 'Billing Address',
                'billing_city' => 'Billing City',
                'billing_county' => 'Billing County',
                'billing_state' => 'Billing State',
                'billing_postal_code' => 'Billing Postal Code',
                'billing_country' => 'Billing Country',
                'sales_person_id' => 'Sales Person 1 Identifier',
                'sales_person_1' => 'Sales Person 1 Name',
                'sales_person1_id' => 'Sales Person 2 Identifier',
                'sales_person_2' => 'Sales Person 2 Name',
                'deliver_at' => 'Delivery Date',
                'tax_profile' => 'Tax Profile',
                'unit_id' => 'Unit Identifier',
                'unit_title' => 'Unit Title',
                'unit_stock' => 'Unit Stock #',
                'unit_vin' => 'Unit VIN',
                'unit_type' => 'Unit Type',
                'unit_category' => 'Unit Category',
                'unit_year' => 'Unit Year',
                'unit_mfg' => 'Unit Mfg',
                'unit_model' => 'Unit Model',
                'unit_gvwr' => 'Unit GVWR',
                'unit_brand' => 'Unit Make',
                'unit_location_id' => 'Unit Location Identifier',
                'unit_location' => 'Unit Location',
                'unit_condition' => 'Unit N/U/D',
                'unit_retail_price' => 'Unit Retail Price',
                'unit_discount' => 'Unit Discount',
                'unit_total_after_discount' => 'Unit Total after Discount (Sale Price)',
                'unit_total_tax_rate' => 'Unit Total Tax Rate',
                'unit_total_sales_tax_amount' => 'Unit Total Sales Tax Amount',
                'unit_state_tax_rate' => 'Unit State Tax Rate',
                'unit_state_tax_amount' => 'Unit State Tax Amount',
                'unit_county_tax_rate' => 'Unit County Tax Rate',
                'unit_county_tax_amount' => 'Unit County Tax Amount',
                'unit_local_tax_rate' => 'Unit Local Tax Rate',
                'unit_local_tax_amount' => 'Unit Local Tax Amount',
                'unit_other_tax_rate' => 'Unit Other Tax Rate',
                'unit_other_tax_amount' => 'Unit Other Tax Amount',
                'unit_cost' => 'Cost of Unit',
                'unit_cost_of_shipping' => 'Cost of Shipping',
                'unit_cost_of_ros' => 'Cost of Ros',
                'unit_cost_of_prep' => 'Cost of Prep',
                'unit_total_cost' => 'Total Cost',
                'unit_true_cost' => 'True Cost',
                'unit_associated_bill_no' => 'Associated Bill No.',
                'unit_total_true_cost' => 'Total True Cost',
                'unit_pac_adj' => 'Pac Adj',
                'unit_cost_overhead_percent' => 'Overhead %',
                'unit_cost_plus_overhead' => 'Cost + Overhead',
                'unit_min_selling_price' => 'Min. Selling Price',
                'unit_floorplan_vendor' => 'Floorplan Vendor',
                'unit_floorplan_committed_date' => 'Floorplan Committed Date',
                'unit_floorplan_balance' => 'Floorplan Balance',
                'trade_in_trade_value' => 'Trade in Value/Allowance',
                'trade_in_lien_payoff_amount' => 'Trade in Lien Payoff Amount',
                'trade_in_net_trade' => 'Trade in Net Trade',
                'additional_pricing_price' => 'Additional Pricing Price',
                'additional_pricing_cost' => 'Additional Pricing Cost',
                'additional_pricing_state_tax' => 'Additional Pricing State Tax',
                'additional_pricing_county_tax' => 'Additional Pricing County Tax',
                'additional_pricing_local_tax' => 'Additional Pricing Local Tax',
                'part_cost' => 'Part Cost',
                'part_total_price' => 'Part Total Price',
                'part_discount' => 'Part Discount',
                'part_tax_rate_applied' => 'Part Tax Rate Applied',
                'part_state_tax_amount' => 'Part State Tax Amount',
                'part_county_tax_amount' => 'Part County Tax Amount',
                'part_local_tax_amount' => 'Part Local Tax Amount',
                'total_parts_tax_amount' => 'Total Parts Tax Amount',
                'labor_subtotal' => 'Labor Subtotal',
                'labor_discount' => 'Labor Discount',
                'labor_tax_rate_applied' => 'Labor Tax Rate Applied',
                'labor_state_tax_amount' => 'Labor State Tax Amount',
                'labor_county_tax_amount' => 'Labor County Tax Amount',
                'labor_local_tax_amount' => 'Labor Local Tax Amount',
                'labor_total_tax_amount' => 'Labor Total Tax Amount',
                'invoice_nontaxable_total' => 'Invoice Nontaxable Total',
                'invoice_taxable_total' => 'Invoice Taxable Total',
                'state_tax_total' => 'State Tax Total',
                'county_tax_total' => 'County Tax Total',
                'city_tax_total' => 'City Tax Total',
                'warranty_tax_total' => 'Warranty Tax Total',
                'other_taxes_total' => 'Other Taxes Total',
                'total_invoice_tax' => 'Total Invoice Tax',
                'total_amount_due' => 'Total Amount Due',
                'payment_type' => 'Payment Type',
                'payment_date' => 'Payment Date',
                'payment_received_total_amount' => 'Payments Received Total Amount',
                'remaining_balance' => 'Remaining Balance',
                'use_local_tax' => 'Use Customer address for tax'
            ])
            ->export();
    }
}
