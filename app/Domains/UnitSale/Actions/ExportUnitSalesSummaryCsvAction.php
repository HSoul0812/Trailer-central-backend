<?php

namespace App\Domains\UnitSale\Actions;

use App\Exceptions\EmptyPropValueException;
use App\Exceptions\File\FileUploadException;
use App\Models\CRM\Account\InvoiceItem;
use App\Models\CRM\Dms\Quickbooks\Item;
use App\Models\CRM\Dms\ServiceOrder;
use App\Models\Inventory\Inventory;
use App\Models\User\User;
use Carbon\Carbon;
use DB;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Query\Builder;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use League\Csv\CannotInsertRecord;
use League\Csv\Exception;
use League\Csv\Writer;
use Storage;
use Throwable;

/**
 * This action will export the Unit Sales Summary report as a CSV file
 * we created this for the ticket https://operatebeyond.atlassian.net/browse/DMSS-1001
 */
class ExportUnitSalesSummaryCsvAction
{
    const REPORT_PATH_S3 = 'reports/unit-sales-summary';

    /** @var FilesystemAdapter */
    private $storage;

    /**
     * The mapping between DB columns -> CSV columns
     * IMPORTANT: Do not change DB columns unless you also change the column aliases in the query
     *
     * @var string[]
     */
    private $headers = [
        'invoice_no' => 'Invoice No.',
        'invoice_date' => 'Invoice Date',
        'invoice_type' => 'Invoice Type',
        'invoice_sales_location' => 'Invoice (Sales) Location',
        'buyer_display_name' => 'Buyer Display Name',
        'tax_exempt' => 'Tax Exempt: True/False',
        'tax_id_number' => 'Tax ID Number',
        'wholesale_customer' => 'Wholesale Customer: True/False',
        'default_discount' => 'Default Discount',
        'billing_address' => 'Billing Address',
        'billing_city' => 'Billing City',
        'billing_county' => 'Billing County',
        'billing_state' => 'Billing State',
        'billing_postal_code' => 'Billing Postal Code',
        'billing_country' => 'Billing Country',
        'sales_person_1' => 'Sales Person 1',
        'sales_person_2' => 'Sales Person 2',
        'tax_profile' => 'Tax Profile',
        'unit_stock' => 'Unit Stock #',
        'unit_vin' => 'Unit VIN',
        'unit_type' => 'Unit Type',
        'unit_category' => 'Unit Category',
        'unit_year' => 'Unit Year',
        'unit_mfg' => 'Unit Mfg',
        'unit_model' => 'Unit Model',
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
    ];

    /** @var User */
    private $dealer;

    /** @var Carbon */
    private $from;

    /** @var Carbon */
    private $to;

    /** @var string */
    private $filename;

    /** @var Collection<int, object> */
    private $rows;

    /**
     * This is the headers that we want to use for the extra unit in the same invoice
     *
     * @var array<string, bool>
     */
    private $headersForExtraUnitRows = [
        'invoice_no' => true,
        'invoice_date' => true,
        'invoice_type' => true,
        'invoice_sales_location' => true,
        'buyer_display_name' => true,
        'tax_exempt' => true,
        'tax_id_number' => true,
        'wholesale_customer' => true,
        'default_discount' => true,
        'billing_address' => true,
        'billing_city' => true,
        'billing_county' => true,
        'billing_state' => true,
        'billing_postal_code' => true,
        'billing_country' => true,
        'sales_person_1' => true,
        'sales_person_2' => true,
        'tax_profile' => true,
        'unit_stock' => true,
        'unit_vin' => true,
        'unit_type' => true,
        'unit_category' => true,
        'unit_year' => true,
        'unit_mfg' => true,
        'unit_model' => true,
        'unit_location' => true,
        'unit_condition' => true,
        'unit_retail_price' => true,
        'unit_discount' => true,
        'unit_total_after_discount' => true,
        'unit_total_tax_rate' => true,
        'unit_total_sales_tax_amount' => true,
        'unit_state_tax_rate' => true,
        'unit_state_tax_amount' => true,
        'unit_county_tax_rate' => true,
        'unit_county_tax_amount' => true,
        'unit_local_tax_rate' => true,
        'unit_local_tax_amount' => true,
        'unit_other_tax_rate' => true,
        'unit_other_tax_amount' => true,
        'unit_cost' => true,
        'unit_cost_of_shipping' => true,
        'unit_cost_of_ros' => true,
        'unit_cost_of_prep' => true,
        'unit_total_cost' => true,
        'unit_true_cost' => true,
        'unit_associated_bill_no' => true,
        'unit_total_true_cost' => true,
        'unit_pac_adj' => true,
        'unit_cost_overhead_percent' => true,
        'unit_cost_plus_overhead' => true,
        'unit_min_selling_price' => true,
        'unit_floorplan_vendor' => true,
        'unit_floorplan_committed_date' => true,
        'unit_floorplan_balance' => true,
    ];

    /**
     * The list of headers that we want to print to the file for the duplicate unit row
     * (the extra row that already has the same unit stock printed previously).
     *
     * @var array<string, bool>
     */
    private $headersForDuplicateUnitRows = [
        'invoice_no' => true,
        'invoice_date' => true,
        'invoice_type' => true,
        'invoice_sales_location' => true,
        'buyer_display_name' => true,
        'tax_exempt' => true,
        'tax_id_number' => true,
        'wholesale_customer' => true,
        'default_discount' => true,
        'billing_address' => true,
        'billing_city' => true,
        'billing_county' => true,
        'billing_state' => true,
        'billing_postal_code' => true,
        'billing_country' => true,
        'sales_person_1' => true,
        'sales_person_2' => true,
        'tax_profile' => true,
        'payment_type' => true,
        'payment_date' => true,
    ];

    public function __construct()
    {
        // By default, we'll use S3 storage because it's the best option for production ENV
        $this->storage = Storage::disk('s3');

        $this->rows = collect([]);
    }

    /**
     * Generate the report and export it to the storage using
     * the file name as the provided $filename
     *
     * @return void
     *
     * @throws CannotInsertRecord
     * @throws EmptyPropValueException
     * @throws Exception
     * @throws FileNotFoundException
     * @throws FileUploadException
     * @throws Throwable
     */
    public function execute(): string
    {
        $this->prepareAction();

        $tmpStorage = Storage::disk('tmp');
        $writer = Writer::createFromPath($tmpStorage->path($this->filename), 'w+');

        // Start by inserting the headers row
        $writer->insertOne(array_values($this->headers));

        // Fetch data from DB and write to the writer
        $this->fetchAndWriteReportDataToCsv($writer);

        // Output the content from the buffer to the actual CSV file
        $tmpStorage->put($this->filename, $writer->toString());

        // Upload CSV File to S3
        $s3FilePath = $this->getS3FilePath();
        $result = $this->storage->putStream($s3FilePath, $tmpStorage->readStream($this->filename));

        throw_if(!$result, new FileUploadException("Can't upload CSV file to S3, please check configuration variables."));

        return $this->storage->url($s3FilePath);
    }

    /**
     * Call to set another storage to use when exporting the file
     *
     * @param FilesystemAdapter $storage
     * @return ExportUnitSalesSummaryCsvAction
     */
    public function useStorage(FilesystemAdapter $storage): ExportUnitSalesSummaryCsvAction
    {
        $this->storage = $storage;

        return $this;
    }

    /**
     * Overwrite the headers, send the mapping of <string, string> with the first
     * string being the codename of the header like buyer_display_name and the second
     * string is the actual header value (label)
     *
     * For example:
     * $action->mergeHeaders(['buyer_display_name' => 'Custom Buyer Display Name']);
     *
     * @param array<string, string> $headers
     * @return ExportUnitSalesSummaryCsvAction
     */
    public function mergeHeaders(array $headers): ExportUnitSalesSummaryCsvAction
    {
        // Remove any headers that we don't have in the $headers array
        // so, we don't accidentally add it to the CSV file
        $headers = array_filter($headers, function (string $headerKey) {
            return array_key_exists($headerKey, $this->headers);
        }, ARRAY_FILTER_USE_KEY);

        $this->headers = array_merge($this->headers, $headers);

        return $this;
    }

    /**
     * @return string[]
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @param User $dealer
     * @return ExportUnitSalesSummaryCsvAction
     */
    public function fromDealer(User $dealer): ExportUnitSalesSummaryCsvAction
    {
        $this->dealer = $dealer;

        return $this;
    }

    /**
     * @param Carbon $from
     * @return ExportUnitSalesSummaryCsvAction
     */
    public function from(Carbon $from): ExportUnitSalesSummaryCsvAction
    {
        $this->from = $from;

        return $this;
    }

    /**
     * @param Carbon $to
     * @return ExportUnitSalesSummaryCsvAction
     */
    public function to(Carbon $to): ExportUnitSalesSummaryCsvAction
    {
        $this->to = $to;

        return $this;
    }

    /**
     * Set the file name
     *
     * @param string $filename
     * @return ExportUnitSalesSummaryCsvAction
     */
    public function withFilename(string $filename): ExportUnitSalesSummaryCsvAction
    {
        // Add the .csv extension if the class user doesn't provide it with the file name
        if (!Str::contains($filename, '.csv')) {
            $filename .= '.csv';
        }

        $this->filename = $filename;

        return $this;
    }

    /**
     * Get the default file name of the report
     *
     * @return string
     */
    private function getDefaultFileName(): string
    {
        return sprintf("unit-sales-summary-dealer-%d-%s.csv", $this->dealer->dealer_id, now()->format('YmdHis'));
    }

    /**
     * Validate the prop values
     *
     * @throws EmptyPropValueException
     */
    private function prepareAction()
    {
        $mappings = [
            'dealer' => $this->dealer,
            'from' => $this->from,
            'to' => $this->to,
        ];

        foreach ($mappings as $propName => $value) {
            if (empty($value)) {
                throw EmptyPropValueException::make($propName);
            }
        }

        $this->filename = $this->filename ?? $this->getDefaultFileName();
    }

    /**
     * Main method to read if you want to understand the main logic of this action
     *
     * @param Writer $writer
     * @return void
     * @throws CannotInsertRecord
     */
    private function fetchAndWriteReportDataToCsv(Writer $writer)
    {
        $headersOrder = array_keys($this->headers);

        $this->rows = $this->getQueryBuilder()->get();

        /** @var object $row */
        foreach ($this->rows as $rowIndex => $ignored) {
            $csvRow = $this->transformDBRowToResultRow($rowIndex, $headersOrder);

            $writer->insertOne($csvRow);
        }
    }

    private function getQueryBuilder(): Builder
    {
        return DB::query()
            ->select([
                'qb_invoices.unit_sale_id as unit_sale_id',
                'qb_invoices.id as invoice_no',
                'qb_invoices.invoice_date as invoice_date',
                DB::raw("'Unit Sale' as invoice_type"),
                'dealer_location.name as invoice_sales_location',
                'dms_customer.display_name as buyer_display_name',
                DB::raw("if(dms_customer.tax_exempt = 1, 'TRUE', 'FALSE') as tax_exempt"),
                DB::raw("coalesce(dms_customer.tax_id_number, '') as tax_id_number"),
                DB::raw("if(dms_customer.is_wholesale = 1, 'TRUE', 'FALSE') as wholesale_customer"),
                'dms_customer.default_discount_percent as default_discount',
                DB::raw("coalesce(dms_customer.address, '') as billing_address"),
                DB::raw("coalesce(dms_customer.city, '') as billing_city"),
                DB::raw("coalesce(dms_customer.county, '') as billing_county"),
                DB::raw("coalesce(dms_customer.region, '') as billing_state"),
                DB::raw("coalesce(dms_customer.postal_code, '') as billing_postal_code"),
                DB::raw("coalesce(dms_customer.country, '') as billing_country"),
                'dms_unit_sale.sales_person_id',
                DB::raw("CONCAT(sales_person_1.first_name, ' ', sales_person_1.last_name) as sales_person_1"),
                DB::raw("coalesce(concat(sales_person_2.first_name, ' ', sales_person_2.last_name), '') as sales_person_2"),
                'dms_unit_sale.tax_profile as tax_profile',
                'inventory.stock as unit_stock',
                DB::raw("coalesce(inventory.vin, '') as unit_vin"),
                'eav_entity_type.title as unit_type',
                DB::raw("coalesce(if(inventory_category.inventory_category_id is not null, inventory_category.label, inventory_category_legacy.label), '') as unit_category"),
                'inventory.year as unit_year',
                'inventory.manufacturer as unit_mfg',
                'inventory.model as unit_model',
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
                DB::raw("
                    coalesce((
                        select sum(trade_in_trade_value_trade_in.trade_value)
                        from dms_unit_sale_trade_in_v1 as trade_in_trade_value_trade_in
                        where trade_in_trade_value_trade_in.unit_sale_id = dms_unit_sale.id
                    ), 0) as trade_in_trade_value
                "),
                DB::raw("
                    coalesce((
                        select sum(coalesce(trade_in_trade_value_trade_in.lien_payoff_amount, 0))
                        from dms_unit_sale_trade_in_v1 as trade_in_trade_value_trade_in
                        where trade_in_trade_value_trade_in.unit_sale_id = dms_unit_sale.id
                    ), 0) as trade_in_lien_payoff_amount
                "),
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
                DB::raw("
                    (
                        select sum(part_total_taxable_amount_accessory.qty * part_total_taxable_amount_accessory.price)
                        from dms_unit_sale_accessory as part_total_taxable_amount_accessory
                        where qb_invoices.unit_sale_id = part_total_taxable_amount_accessory.unit_sale_id
                        and part_total_taxable_amount_accessory.taxable = 1
                    ) as part_total_taxable_amount
                "),
                DB::raw("
                    (
                        select sum(part_total_taxable_amount_accessory.qty * part_total_taxable_amount_accessory.price)
                        from dms_unit_sale_accessory as part_total_taxable_amount_accessory
                        where qb_invoices.unit_sale_id = part_total_taxable_amount_accessory.unit_sale_id
                        and part_total_taxable_amount_accessory.taxable = 0
                    ) as part_total_nontaxable_amount
                "),
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
            ->from('qb_invoice_item_inventories')
            ->leftJoin('qb_invoice_items', 'qb_invoice_items.id', '=', 'qb_invoice_item_inventories.invoice_item_id')
            ->leftJoin('qb_invoices', 'qb_invoice_items.invoice_id', '=', 'qb_invoices.id')
            ->leftJoin('dms_unit_sale', 'qb_invoices.unit_sale_id', '=', 'dms_unit_sale.id')
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
            ->whereBetween('qb_invoices.invoice_date', [$this->from, $this->to])
            ->orderBy('qb_invoices.invoice_date');
    }

    /**
     * @param int $rowIndex
     * @param array $headers
     * @return void
     */
    private function transformDBRowToResultRow(int $rowIndex, array $headers): array
    {
        $row = $this->rows[$rowIndex];

        $this->populateManualData($row);

        $hasTheSameUnitPrintedOnThePreviousRows = $this->hasTheSameUnitPrintedOnThePreviousRows($rowIndex);

        return array_map(function (string $header) use ($row, $hasTheSameUnitPrintedOnThePreviousRows) {
            // If the same invoice has more than one unit of the same stock #, we will print
            // only the necessary columns from the 2nd row onward
            if ($hasTheSameUnitPrintedOnThePreviousRows) {
                if (array_key_exists($header, $this->headersForDuplicateUnitRows)) {
                    return object_get($row, $header);
                } else {
                    return '';
                }
            }

            // For the non-main unit, we print only some important columns
            if (!$row->unit_is_main) {
                if (array_key_exists($header, $this->headersForExtraUnitRows)) {
                    return object_get($row, $header);
                } else {
                    return '';
                }
            }

            // For the first unit, generate everything as usual
            return object_get($row, $header);
        }, $headers);
    }

    /**
     * @param object $row
     * @return void
     */
    protected function populateManualData(object $row): void
    {
        $this->populateUnitTotalData($row);
        $this->populateAdditionalPricingData($row);
        $this->populatePartData($row);
        $this->populateLaborData($row);
        $this->populateInvoiceTotalData($row);
    }

    /**
     * @return string
     */
    public function getS3FilePath(): string
    {
        return sprintf("/%s/%s", self::REPORT_PATH_S3, $this->filename);
    }

    private function populateUnitTotalData(object $row)
    {
        // TODO: if unit_tax_before_trade is 1 then the tax amount doesn't count the trade in amount

        $row->unit_total_after_discount = $row->unit_retail_price - $row->unit_discount;

        $row->unit_total_after_discount_less_trade_in = $row->unit_total_after_discount;

        // For the main inventory, the value after trade in needs to be calculated by
        // deducting the amount of trade in value
        if ($row->unit_is_main) {
            $row->unit_total_after_discount_less_trade_in -= $row->trade_in_trade_value;
        }

        // The amount to calculate tax depends on either the unit has tax_before_trade as on or off
        // on means we apply the taxes directly to the unit amount without deducting the trade in amount
        // off means we will apply the taxes after the trade in amount is deducted from the unit amount
        $amountToCalculateTax = $row->unit_tax_before_trade
            ? $row->unit_total_after_discount
            : $row->unit_total_after_discount_less_trade_in;

        $row->unit_sale_metadata = json_decode($row->unit_sale_metadata, true);

        $row->unit_state_tax_rate = $this->convertTaxRateToPercentage(
            data_get($row->unit_sale_metadata, 'taxRates.inventory.stateTaxRate', 0)
        );

        $row->unit_state_tax_amount = round(($row->unit_state_tax_rate / 100) * $amountToCalculateTax, 2);

        $row->unit_county_tax_rate = $this->convertTaxRateToPercentage(
            data_get($row->unit_sale_metadata, 'taxRates.inventory.countyTaxRate', 0)
        );

        $row->unit_county_tax_amount = round(($row->unit_county_tax_rate / 100) * $amountToCalculateTax, 2);

        $row->unit_local_tax_rate = array_sum([
            $this->convertTaxRateToPercentage(
                data_get($row->unit_sale_metadata, 'taxRates.inventory.cityTaxRate', 0)
            ),
            $this->convertTaxRateToPercentage(
                data_get($row->unit_sale_metadata, 'taxRates.inventory.district1TaxRate', 0)
            ),
            $this->convertTaxRateToPercentage(
                data_get($row->unit_sale_metadata, 'taxRates.inventory.district2TaxRate', 0)
            ),
            $this->convertTaxRateToPercentage(
                data_get($row->unit_sale_metadata, 'taxRates.inventory.district3TaxRate', 0)
            ),
            $this->convertTaxRateToPercentage(
                data_get($row->unit_sale_metadata, 'taxRates.inventory.district4TaxRate', 0)
            ),
        ]);

        $row->unit_local_tax_amount = round(($row->unit_local_tax_rate / 100) * $amountToCalculateTax, 2);

        $row->unit_other_tax_rate = array_sum([
            $this->convertTaxRateToPercentage(
                data_get($row->unit_sale_metadata, 'taxRates.inventory.dmvTaxRate', 0)
            ),
            $this->convertTaxRateToPercentage(
                data_get($row->unit_sale_metadata, 'taxRates.inventory.registrationTaxRate', 0)
            ),
        ]);

        $row->unit_other_tax_amount = round(($row->unit_other_tax_rate / 100) * $amountToCalculateTax, 2);

        $row->unit_total_tax_rate = array_sum([
            $row->unit_state_tax_rate,
            $row->unit_county_tax_rate,
            $row->unit_local_tax_rate,
            $row->unit_other_tax_rate,
        ]);

        $row->unit_total_sales_tax_amount = round(($row->unit_total_tax_rate / 100) * $amountToCalculateTax, 2);

        $row->unit_total_cost = array_sum([
            $row->unit_cost,
            $row->unit_cost_of_shipping,
            $row->unit_cost_of_ros,
            $row->unit_cost_of_prep,
        ]);

        $row->unit_total_true_cost = array_sum([
            $row->unit_true_cost,
            $row->unit_cost_of_shipping,
            $row->unit_cost_of_ros,
            $row->unit_cost_of_prep,
        ]);

        // The unit_pac_adj is the full amount when the type is 'amount'
        // in the case of type is 'percent', we need to calculate the amount
        // by seeing the unit_pac_amount as the percentage of the unit_total_cost
        $row->unit_pac_adj = $row->unit_pac_type === Inventory::PAC_TYPE_AMOUNT
            ? $row->unit_pac_amount
            : round(($row->unit_pac_amount / 100) * $row->unit_total_cost, 2);

        // Prevent Divide By Zero error
        $safeUnitTotalCost = empty($row->unit_total_cost) ? 1 : $row->unit_total_cost;

        // The unit_cost_overhead_percent is the full amount when the type is 'percent'
        // in the case of type is 'amount', we'll need to calculate the percent using
        // the unit_total_cost
        $row->unit_cost_overhead_percent = $row->unit_pac_type === Inventory::PAC_TYPE_PERCENT
            ? $row->unit_pac_amount
            : round(($row->unit_pac_amount / $safeUnitTotalCost) * 100, 2);

        $row->unit_cost_plus_overhead = $row->unit_total_cost + $row->unit_pac_adj;

        $row->trade_in_net_trade = $row->trade_in_trade_value - $row->trade_in_lien_payoff_amount;
    }

    private function populateAdditionalPricingData(object $row)
    {
        $stateTaxRate = $this->convertTaxRateToPercentage(
            data_get($row->unit_sale_metadata, 'taxRates.pricing.stateTaxRate', 0)
        );

        $row->additional_pricing_state_tax = round(($stateTaxRate / 100) * $row->additional_pricing_state_taxable_amount, 2);

        $countyTaxRate = $this->convertTaxRateToPercentage(
            data_get($row->unit_sale_metadata, 'taxRates.pricing.countyTaxRate', 0)
        );

        $row->additional_pricing_county_tax = round(($countyTaxRate / 100) * $row->additional_pricing_county_taxable_amount, 2);

        $localTaxRate = array_sum([
            $this->convertTaxRateToPercentage(
                data_get($row->unit_sale_metadata, 'taxRates.pricing.cityTaxRate', 0)
            ),
            $this->convertTaxRateToPercentage(
                data_get($row->unit_sale_metadata, 'taxRates.pricing.district1TaxRate', 0)
            ),
            $this->convertTaxRateToPercentage(
                data_get($row->unit_sale_metadata, 'taxRates.pricing.district2TaxRate', 0)
            ),
            $this->convertTaxRateToPercentage(
                data_get($row->unit_sale_metadata, 'taxRates.pricing.district3TaxRate', 0)
            ),
            $this->convertTaxRateToPercentage(
                data_get($row->unit_sale_metadata, 'taxRates.pricing.district4TaxRate', 0)
            ),
        ]);

        $row->additional_pricing_local_tax = round(($localTaxRate / 100) * $row->additional_pricing_local_taxable_amount, 2);

        $row->additional_pricing_total_tax_rate = array_sum([
            $stateTaxRate,
            $countyTaxRate,
            $localTaxRate,
        ]);
    }

    private function populatePartData(object $row)
    {
        $stateTaxRate = $this->convertTaxRateToPercentage(
            data_get($row->unit_sale_metadata, 'taxRates.pricing.stateTaxRate', 0)
        );

        $countyTaxRate = $this->convertTaxRateToPercentage(
            data_get($row->unit_sale_metadata, 'taxRates.pricing.countyTaxRate', 0)
        );

        $localTaxRate = array_sum([
            $this->convertTaxRateToPercentage(
                data_get($row->unit_sale_metadata, 'taxRates.pricing.cityTaxRate', 0)
            ),
            $this->convertTaxRateToPercentage(
                data_get($row->unit_sale_metadata, 'taxRates.pricing.district1TaxRate', 0)
            ),
            $this->convertTaxRateToPercentage(
                data_get($row->unit_sale_metadata, 'taxRates.pricing.district2TaxRate', 0)
            ),
            $this->convertTaxRateToPercentage(
                data_get($row->unit_sale_metadata, 'taxRates.pricing.district3TaxRate', 0)
            ),
            $this->convertTaxRateToPercentage(
                data_get($row->unit_sale_metadata, 'taxRates.pricing.district4TaxRate', 0)
            )
        ]);

        $row->part_tax_rate_applied = array_sum([
            $stateTaxRate,
            $countyTaxRate,
            $localTaxRate,
        ]);

        // TODO: Revisit how to calculate the part when we have both taxable and non taxable path

        $row->part_total_taxable_amount_after_discount = $row->part_total_taxable_amount - $row->part_discount;

        $row->part_state_tax_amount = round(($stateTaxRate / 100) * $row->part_total_taxable_amount_after_discount);

        $row->part_county_tax_amount = round(($countyTaxRate / 100) * $row->part_total_taxable_amount_after_discount);

        $row->part_local_tax_amount = round(($localTaxRate / 100) * $row->part_total_taxable_amount_after_discount);

        $row->total_parts_tax_amount = array_sum([
            $row->part_state_tax_amount,
            $row->part_county_tax_amount,
            $row->part_local_tax_amount,
        ]);
    }

    private function populateLaborData(object $row)
    {
        // IF labor_tax_type_from_location is not_tax OR total tax rate is 0, then there is no tax


        $row->labor_total_after_discount = $row->labor_subtotal - $row->labor_discount;

        // TODO: Fix this, the first step is to check if the location has Not Tax Labor enabled
        //  if so, then all the labor will be nontaxable
        $row->labor_taxable_amount = $row->labor_total_after_discount;

        // TODO: The second step is with the RO, in the RO only some labors are taxable, we need
        //  to check that accordingly as well

        $stateTaxRate = $this->convertTaxRateToPercentage(
            data_get($row->unit_sale_metadata, 'taxRates.labor.stateTaxRate', 0)
        );

        $countyTaxRate = $this->convertTaxRateToPercentage(
            data_get($row->unit_sale_metadata, 'taxRates.labor.countyTaxRate', 0)
        );

        $localTaxRate = array_sum([
            $this->convertTaxRateToPercentage(
                data_get($row->unit_sale_metadata, 'taxRates.labor.cityTaxRate', 0)
            ),
            $this->convertTaxRateToPercentage(
                data_get($row->unit_sale_metadata, 'taxRates.labor.district1TaxRate', 0)
            ),
            $this->convertTaxRateToPercentage(
                data_get($row->unit_sale_metadata, 'taxRates.labor.district2TaxRate', 0)
            ),
            $this->convertTaxRateToPercentage(
                data_get($row->unit_sale_metadata, 'taxRates.labor.district3TaxRate', 0)
            ),
            $this->convertTaxRateToPercentage(
                data_get($row->unit_sale_metadata, 'taxRates.labor.district4TaxRate', 0)
            ),
        ]);

        $row->labor_tax_rate_applied = array_sum([
            $stateTaxRate,
            $countyTaxRate,
            $localTaxRate,
        ]);

        $row->labor_state_tax_amount = round(($stateTaxRate / 100) * $row->labor_total_after_discount, 2);

        $row->labor_county_tax_amount = round(($countyTaxRate / 100) * $row->labor_total_after_discount, 2);

        $row->labor_local_tax_amount = round(($localTaxRate / 100) * $row->labor_total_after_discount, 2);

        $row->labor_total_tax_amount = array_sum([
            $row->labor_state_tax_amount,
            $row->labor_county_tax_amount,
            $row->labor_local_tax_amount,
        ]);
    }

    private function populateInvoiceTotalData(object $row)
    {
        $row->unit_taxable_amount = $row->unit_total_after_discount_less_trade_in;

        if (empty($row->unit_total_tax_rate)) {
            $row->unit_nontaxable_amount = $row->unit_taxable_amount;
            $row->unit_taxable_amount = 0;
        }

        if (empty($row->additional_pricing_total_tax_rate)) {
            $row->additional_pricing_nontaxable_amount += $row->additional_pricing_taxable_amount;
            $row->additional_pricing_taxable_amount = 0;
        }

        if (empty($row->part_tax_rate_applied)) {
            $row->part_total_nontaxable_amount += $row->part_total_taxable_amount_after_discount;
            $row->part_total_taxable_amount_after_discount = 0;
        }

        if (empty($row->labor_tax_rate_applied)) {
            $row->labor_nontaxable_amount += $row->labor_taxable_amount;
            $row->labor_taxable_amount = 0;
        }

        $row->invoice_taxable_total = array_sum([
            $row->unit_taxable_amount,
            $row->additional_pricing_taxable_amount,
            $row->part_total_taxable_amount_after_discount,
            $row->labor_taxable_amount,
        ]);

        $row->invoice_nontaxable_total = array_sum([
            $row->unit_nontaxable_amount,
            $row->additional_pricing_nontaxable_amount,
            $row->part_total_nontaxable_amount,
            $row->labor_nontaxable_amount,
        ]);

        // - warranty_tax_total
        // Warranty Tax Total - Would be the Tax of the RO IF type = Warranty

        // - other_taxes_total
        // Other Taxes Total - total $ Amount of non State, County or Local(?) Taxes. Will double check if Local is separate or included in Other
        // probably shop supply taxes

        $row->total_invoice_tax = $row->total_amount_due - ($row->invoice_taxable_total + $row->invoice_nontaxable_total);

        $row->remaining_balance = $row->total_amount_due - $row->payment_received_total_amount;
    }

    /**
     * Convert the tax rate like 0.06 to the percentage like 6
     *
     * @param float $taxRate
     * @return float
     */
    private function convertTaxRateToPercentage(float $taxRate): float
    {
        return round($taxRate * 100, 2);
    }

    /**
     * Check if we ever have the same unit with the given rowIndex printed in the file
     *
     * @param int $rowIndex
     * @return bool
     */
    private function hasTheSameUnitPrintedOnThePreviousRows(int $rowIndex): bool
    {
        $row = $this->rows[$rowIndex];

        for ($i = 0; $i < $rowIndex; $i++) {
            $currentRow = $this->rows[$i];

            if ($row->unit_stock === $currentRow->unit_stock) {
                return true;
            }
        }

        return false;
    }
}
