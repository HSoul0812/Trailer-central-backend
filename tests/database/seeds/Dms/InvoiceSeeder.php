<?php


namespace Tests\database\seeds\Dms;

use App\Models\CRM\Account\Invoice;
use App\Models\CRM\Account\InvoiceItem;
use App\Models\CRM\Dms\ServiceOrder;
use App\Traits\WithGetter;
use Tests\database\seeds\Seeder;
use App\Models\CRM\Dms\Quickbooks\Item;

/**
 * Class InvoiceSeeder
 * @package Tests\database\seeds\Dms
 *
 * @property-read Invoice $invoice
 * @property-read InvoiceItem $invoiceItem
 * @property-read Item $item
 */
class InvoiceSeeder extends Seeder
{
    use WithGetter;

    /**
     * @var array
     */
    private $params;

    /**
     * @var Invoice
     */
    private $invoice;

    /**
     * @var InvoiceItem
     */
    private $invoiceItem;

    /**
     * @var Item
     */
    private $item;

    public function __construct(array $params = [])
    {
        $this->params = $params;
    }

    public function seed(): void
    {
        $invoiceParams = [];
        $itemParams = [];

        if ($this->params['serviceOrder'] instanceof ServiceOrder) {
            $invoiceParams = [
                'dealer_id' => $this->params['serviceOrder']->dealer_id,
                'repair_order_id' => $this->params['serviceOrder']->id,
            ];

            $itemParams = [
                'dealer_id' => $this->params['serviceOrder']->dealer_id,
            ];
        }

        $this->invoice = factory(Invoice::class)->create($invoiceParams);
        $this->item = factory(Item::class)->create($itemParams);

        $invoiceItemParams = [
            'invoice_id' => $this->invoice->id,
            'item_id' => $this->item->id
        ];

        if (isset($this->params['invoiceItem']['unit_price'])) {
            $invoiceItemParams['unit_price'] = $this->params['invoiceItem']['unit_price'];
        }

        $this->invoiceItem = factory(InvoiceItem::class)->create($invoiceItemParams);
    }

    public function cleanUp(): void
    {
        InvoiceItem::destroy($this->invoiceItem->id);
        Item::destroy($this->item->id);
        Invoice::destroy($this->invoice->id);
    }
}
