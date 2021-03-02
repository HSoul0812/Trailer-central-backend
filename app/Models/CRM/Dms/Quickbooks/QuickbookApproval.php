<?php


namespace App\Models\CRM\Dms\Quickbooks;


use Illuminate\Database\Eloquent\Model;

/**
 * Class QuickbookApproval
 * @package App\Models\CRM\Dms\Quickbooks
 *
 * @property $id
 * @property $dealer_id
 * @property $tb_name
 * @property $tb_primary_id
 * @property $action_type
 * @property $send_to_quickbook
 * @property $qb_obj
 * @property $is_approved
 * @property $sort_order
 * @property $created_at
 * @property $exported_at
 * @property $qb_id
 * @property $error_result
 *
 */
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

    protected $appends = ['tb_label'];

    protected $guarded = ['qb_id'];

    public $timestamps = false;

    public function getTbLabelAttribute()
    {
        return self::TABLE_NAME_MAPPER[$this->tb_name];
    }

    public function getCustomerNameAttribute()
    {
        $qbObj = json_decode($this->qb_obj, true);
        if ($this->tb_name === 'dms_customer' && isset($qbObj['DisplayName'])) {
            return $qbObj['DisplayName'];
        }
        if (isset($qbObj['CustomerRef']) && !empty($qbObj['CustomerRef']['name'])) {
            return $qbObj['CustomerRef']['name'];
        }
        return null;
    }

    public function getPaymentMethodAttribute()
    {
        $qbObj = json_decode($this->qb_obj, true);
        if ($this->tb_name === 'qb_payment_methods' && isset($qbObj['Name'])) {
            return $qbObj['Name'];
        }
        if (isset($qbObj['PaymentMethodRef']) && !empty($qbObj['PaymentMethodRef']['name'])) {
            return $qbObj['PaymentMethodRef']['name'];
        }
        return null;
    }

    public function getSalesTicketNumAttribute()
    {
        $qbObj = json_decode($this->qb_obj, true);
        if ($this->tb_name === 'qb_payment' && isset($qbObj['PaymentRefNum'])) {
            return $qbObj['PaymentRefNum'];
        }
        if (isset($qbObj['DocNumber'])) {
            return $qbObj['DocNumber'];
        }
        return null;
    }

    public function getTicketTotalAttribute()
    {
        $qbObj = json_decode($this->qb_obj, true);
        if ($this->tb_name === 'inventory_floor_plan_payment' && isset($qbObj['Amount'])) {
            return $qbObj['Amount'];
        }
        if (in_array($this->tb_name, ['qb_bill_payment', 'qb_payment']) && isset($qbObj['TotalAmt'])) {
            return $qbObj['TotalAmt'];
        }
        if (
            in_array($this->tb_name, [
                'qb_bills',
                'qb_invoices',
                'crm_pos_sales',
                'qb_expenses',
                'dealer_refunds'
            ]) &&
            isset($qbObj['Line'])
        ) {
            if (is_array($qbObj['Line'])) {
                $totalAmt = 0;
                foreach ($qbObj['Line'] as $item) {
                    if (isset($item['Amount'])) {
                        $totalAmt += (float) $item['Amount'];
                    }
                }
                return $totalAmt;
            }
        }
        return null;
    }

    public function scopeFilterByTableName($query, $searchTerm)
    {
        $filteredTables = array_filter(self::TABLE_NAME_MAPPER, function($tableLabel) use($searchTerm) {
            return stripos($tableLabel, $searchTerm) !== false;
        });
        return $query->whereIn('tb_name', array_keys($filteredTables));
    }

}
