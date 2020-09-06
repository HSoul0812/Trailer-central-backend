<?php


namespace App\Models\CRM\Quickbooks;


use Illuminate\Database\Eloquent\Model;

class QuickbookApproval extends Model
{
    // Statuses of Quickbook Approvals
    const TO_SEND = 'to_send';
    const SENT = 'sent';
    const FAILED = 'failed';

    const TABLE_NAME_MAPPER = [
        'qb_accounts' => 'Account',
        'qb_bills' => 'Bill',
        'qb_bill_payment' => 'Bill Payment',
        'qb_vendors' => 'Vendor',
        'qb_payment_methods' => 'Payment Method',
        'dealer_employee' => 'Employee',
        'qb_items' => 'Item',
        'qb_item_category' => 'Item Category',
        'qb_invoices' => 'Invoice',
        'qb_payment' => 'Payment',
        'dms_customer' => 'Customer',
        'crm_pos_sales' => 'POS Sale',
        'qb_journal_entry' => 'Journal Entry',
        'qb_expenses' => 'Expense',
        'qb_items_new' => 'Item (New)',
        'inventory_floor_plan_payment' => 'Floorplan Payment',
        'dealer_refunds' => 'Refunds Receipt',
    ];

    protected $table = 'quickbook_approval';

    public $timestamps = false;

    public function scopeFilterByTableName($query, $searchTerm)
    {
        $filteredTables = array_filter(self::TABLE_NAME_MAPPER, function($tableLabel) use($searchTerm) {
            return stripos($tableLabel, $searchTerm) !== false;
        });
        return $query->whereIn('tb_name', array_keys($filteredTables));
    }

}
