<?php

declare(strict_types=1);

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

/**
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
 *
 * @method static Builder select($columns = ['*'])
 * @method static Builder where($column, $operator = null, $value = null, $boolean = 'and')
 */
class InventoryHistory extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'inventory_transaction_history';

    public $timestamps = false;
}
