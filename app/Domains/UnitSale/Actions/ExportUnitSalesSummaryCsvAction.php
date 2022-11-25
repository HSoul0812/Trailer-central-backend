<?php

namespace App\Domains\UnitSale\Actions;

use App\Exceptions\EmptyPropValueException;
use App\Models\User\User;
use Carbon\Carbon;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Str;
use League\Csv\CannotInsertRecord;
use League\Csv\Exception;
use League\Csv\Writer;
use Storage;

/**
 * This action will export the Unit Sales Summary report as a CSV file
 * we created this for the ticket https://operatebeyond.atlassian.net/browse/DMSS-1001
 */
class ExportUnitSalesSummaryCsvAction
{
    /** @var FilesystemAdapter */
    private $storage;

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
        'unit_nud' => 'Unit N/U/D',
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
        'cost_of_unit' => 'Cost of Unit',
        'cost_of_shipping' => 'Cost of Shipping',
        'cost_of_ros' => 'Cost of Ros',
        'cost_of_prep' => 'Cost of Prep',
        'total_cost' => 'Total Cost',
        'true_cost' => 'True Cost',
        'associated_bill_no' => 'Associated Bill No.',
        'total_true_cost' => 'Total True Cost',
        'pac_adj' => 'Pac Adj',
        'overhead_percentage' => 'Overhead %',
        'cost_plus_overhead' => 'Cost + Overhead',
        'min_selling_price' => 'Min. Selling Price',
        'floorplan_vendor' => 'Floorplan Vendor',
        'floorplan_committed_date' => 'Floorplan Committed Date',
        'floorplan_balance' => 'Floorplan Balance',
        'trade_stock' => 'Trade Stock',
        'trade_vin' => 'Trade VIN',
        'trade_type' => 'Trade Type',
        'trade_category' => 'Trade Category',
        'trade_in_year' => 'Trade In Year',
        'trade_mfg' => 'Trade Mfg',
        'trade_in_brand' => 'Trade in Brand',
        'trade_in_model' => 'Trade in Model',
        'trade_sell_price' => 'Trade Sell Price',
        'trade_value_allowance' => 'Trade Value/Allowance',
        'trade_book_value' => 'Trade Book Value',
        'trade_lien' => 'Trade Lien: Yes/No',
        'trade_lien_payoff_amount' => 'Trade Lien Payoff Amount',
        'trade_net_trade' => 'Trade Net Trade',
        'additional_pricing_price' => 'Additional Pricing Price',
        'additional_pricing_cost' => 'Additional Pricing Cost',
        'additional_pricing_state_tax' => 'Additional Pricing State Tax',
        'additional_pricing_county_tax' => 'Additional Pricing County Tax',
        'additional_pricing_local_tax' => 'Additional Pricing Local Tax',
        'part_cost' => 'Part Cost',
        'part_total_price' => 'Part Total Price',
        'part_discount' => 'Part Discount',
        'part_tax_rate_applied' => 'Part Tax Rate Applied',
        'part_state_tax' => 'Part State Tax',
        'part_county_tax_amount' => 'Part County Tax Amount',
        'part_local_tax_amount' => 'Part Local Tax Amount',
        'total_parts_tax_amount' => 'Total Parts Tax Amount',
        'labor_subtotal' => 'Labor Subtotal',
        'labor_discount' => 'Labor Discount',
        'labor_total_tax' => 'Labor Total Tax',
        'invoice_nontaxable_total' => 'Invoice Nontaxable Total',
        'invoice_taxable_total' => 'Invoice Taxable Total',
        'state_tax_total' => 'State Tax Total',
        'county_tax_total' => 'County Tax Total',
        'city_tax_total' => 'City Tax Total',
        'warranty_tax_total' => 'Warranty Tax Total',
        'other_taxes_total' => 'Other Taxes Total',
        'total_invoice_tax' => 'Total Invoice Tax',
        'total_amount_due' => 'Total Amount Due',
        'payment' => 'Payment',
        'payment_type' => 'Payment Type',
        'payment_date' => 'Payment Date',
        'payments_received_total_amount' => 'Payments Received Total Amount',
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

    public function __construct()
    {
        // By default, we'll use S3 storage because it's the best option for production ENV
        $this->storage = Storage::disk('s3');
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
     */
    public function execute(): string
    {
        $this->prepareAction();

        $tmpStorage = Storage::disk('tmp');
        $writer = Writer::createFromPath($tmpStorage->path($this->filename), 'w+');

        // Start by inserting the headers row
        $writer->insertOne(array_values($this->headers));

        // TODO: Query the data from the database and then start
        //  looping through each data and use ->insertOne to add
        //  them into the CSV file
        //  IMPORTANT: Prepend the path with unit-sales-summary/
        //  before inserting the file into S3.

        // Output the content from the buffer to the actual CSV file
        $tmpStorage->put($this->filename, $writer->toString());

        // TODO: Change so it return path on S3
        return $tmpStorage->path($this->filename);
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
        $this->headers = array_merge($this->headers, $headers);

        return $this;
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
        if (Str::contains($filename, '.csv')) {
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
}
