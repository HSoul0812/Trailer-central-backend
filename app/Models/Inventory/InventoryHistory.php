<?php

declare(strict_types=1);

namespace App\Models\Inventory;

use App\Contracts\Support\DTO;
use App\Traits\WithFactory;
use App\Traits\WithGetter;

/**
 * #[Immutable]
 *
 * Abstraction model for `inventory_transaction_history` view which:
 *      1. Its sources are:
 *          a) repair-order:dms_repair_order
 *          b) quote:dms_unit_sale
 *          c) POS:qb_invoices + crm_pos_sales + dealer_sales_receipt
 *      2. Only customer_id, inventory_id, customer_name and vin columns come from the same source,
 *         THE OTHERS COLUMNS COME FROM DIFFERENT SOURCES, this is why the next properties annotations
 *         have a pseudo-notation in this way: `for source[repair-order|quote|POS]:table_name.column_name`
 *
 * @property-read int $id for repair-order:dms_repair_order.id, quote:dms_unit_sale.id, POS:qb_invoices.id
 * @property-read int $customer_id
 * @property-read int $inventory_id
 * @property-read string $customer_name
 * @property-read string $vin
 * @property-read string $created_at for repair-order:dms_repair_order.created_at, quote:dms_unit_sale.created_at, POS:qb_invoices.invoice_date
 * @property-read string $subtype for repair-order:dms_repair_order.type, POS:qb_invoices.format
 * @property-read string $text_1 for repair-order:dms_repair_order.problem, quote:dms_unit_sale.title, POS:qb_invoices.doc_num
 * @property-read string $text_2 for repair-order:dms_repair_order.cause, quote:dms_unit_sale.admin_note, POS:qb_invoices.memo
 * @property-read string $text_3 for repair-order:dms_repair_order.solution, POS:dealer_sales_receipt.receipt_path
 * @property-read numeric $sub_total for repair-order:dms_service_item.amount, quote:dms_unit_sale.subtotal, POS:(qb_invoice_items.unit_price x qb_invoice_items.qty)
 * @property-read numeric $total for repair-order:dms_repair_order.total_price, quote:dms_unit_sale.total_price, POS:qb_invoices.total
 * @property-read string $type [POS|quote|repair-order]
 */
class InventoryHistory implements DTO
{
    use WithGetter;
    use WithFactory;

    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $customer_id;

    /**
     * @var int
     */
    private $inventory_id;

    /**
     * @var string
     */
    private $customer_name;

    /**
     * @var string
     */
    private $vin;

    /**
     * @var string
     */
    private $created_at;

    /**
     * @var string
     */
    private $subtype;

    /**
     * @var string
     */
    private $text_1;

    /**
     * @var string
     */
    private $text_2;

    /**
     * @var string
     */
    private $text_3;

    /**
     * @var float
     */
    private $sub_total;

    /**
     * @var float
     */
    private $total;

    /**
     * @var string
     */
    private $type;

    public function asArray(): array
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'inventory_id' => $this->inventory_id,
            'customer_name' => $this->customer_name,
            'vin' => $this->vin,
            'created_at' => $this->created_at,
            'subtype' => $this->subtype,
            'text_1' => $this->text_1,
            'text_2' => $this->text_2,
            'text_3' => $this->text_3,
            'sub_total' => $this->sub_total,
            'total' => $this->total,
            'type' => $this->type
        ];
    }
}
