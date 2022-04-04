<?php
namespace App\Models\CRM\Dms\Quickbooks;

use App\Models\Traits\TableAware;
use App\Models\User\Location\QboLocationMapping;
use Illuminate\Database\Eloquent\Collection;
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
 * @method static \Illuminate\Database\Query\Builder select($columns = ['*'])
 * @method static \Illuminate\Database\Query\Builder where($column, $operator = null, $value = null, $boolean = 'and')
 * @method static \Illuminate\Database\Query\Builder whereIn($column, $values, $boolean = 'and', $not = false)
 * @method static QuickbookApproval findOrFail($id, array $columns = ['*'])
 * @method static QuickbookApproval|Collection|static[]|static|null find($id, $columns = ['*'])
 */
class QuickbookApproval extends Model
{
    use TableAware;

    // Statuses of Quickbook Approvals
    const TO_SEND = 'to_send';
    const SENT = 'sent';
    const FAILED = 'failed';
    const REMOVED = 'removed';

    public const ACTION_UPDATE = 'update';
    public const ACTION_ADD = 'add';

    public const PRIORITY_DEALER_LOCATION = 40;

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
        'dealer_location' => 'Dealer location',
    ];

    protected $table = 'quickbook_approval';

    protected $appends = ['tb_label'];

    protected $guarded = ['qb_id'];

    public $timestamps = false;

    protected $listAccountAttrName = [
        'IncomeAccountRef',
        'ExpenseAccountRef',
        'AssetAccountRef',
        'CreditCardAccountRef',
        'BankAccountRef',
        'DepositToAccountRef',
    ];

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

    public function getAccountAttribute(): ?string
    {
        $names = [];
        $tbName = $this->tb_name;
        $qbObj = json_decode($this->qb_obj,true);
        switch($tbName) {
            case 'qb_accounts':
                if(!empty($qbObj['Name'])) {
                    $names[] = $qbObj['Name'];
                }
                break;
            case 'qb_bill_payment':
                if(!empty($qbObj['CreditCardPayment']['CCAccountRef']['name'])) {
                    $names[] = $qbObj['CreditCardPayment']['CCAccountRef']['name'];
                }
                break;
            case 'qb_vendors':
                if(!empty($qbObj['DisplayName'])) {
                    $names[] = $qbObj['DisplayName'];
                }
                break;
            case 'qb_items':
            case 'qb_items_new':
            case 'inventory_floor_plan_payment':
            case 'qb_payment':
                $names = $this->parseNameByItem();
                break;
            case 'qb_bills':
            case 'qb_expenses':
                $names = $this->parseNameInLine('AccountBasedExpenseLineDetail', 'AccountRef');
                if(!empty($qbObj['AccountRef']['name'])) {
                    $names[] = $qbObj['AccountRef']['name'];
                }
                break;
            case 'qb_invoices':
            case 'crm_pos_sales':
            case 'dealer_refunds':
                $names = $this->parseNameInLine('SalesItemLineDetail', 'ItemRef');
                if(!empty($qbObj['DepositToAccountRef']['name'])) {
                    $names[] = $qbObj['DepositToAccountRef']['name'];
                }
                break;
            case 'qb_journal_entry':
                $names = $this->parseNameInLine('JournalEntryLineDetail', 'AccountRef');
                break;
            default:
                break;
        }
        $name = implode('<br/>', $names);
        return $name;
    }

    protected function parseNameByItem():array
    {
        $names = [];
        $qbObj = json_decode($this->qb_obj,true);
        foreach($this->listAccountAttrName as $attr) {
            if(!empty($qbObj[$attr]['name'])) {
                $names[] = $qbObj[$attr]['name'];
            }
        }
        return $names;
    }

    protected function parseNameInLine(string $key, string $subKey):array
    {
        $names = [];
        $qbObj = json_decode($this->qb_obj,true);
        if(!empty($qbObj['Line']) && is_array($qbObj['Line'])) {
            foreach($qbObj['Line'] as $k => $line) {
                if(!empty($line[$key][$subKey]['name'])) {
                    $names[] = $line[$key][$subKey]['name'];
                }
            }
        }

        return $names;

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

    /**
     * @param QuickbookApprovalDeleted $obj
     */
    public function createFromDeleted(QuickbookApprovalDeleted $obj)
    {
        $this->id = $obj->id;
        $this->dealer_id = $obj->dealer_id;
        $this->action_type = $obj->action_type;
        $this->tb_name = $obj->tb_name;
        $this->tb_primary_id = $obj->tb_primary_id;
        $this->send_to_quickbook = $obj->send_to_quickbook;
        $this->qb_obj = $obj->qb_obj;
        $this->is_approved = $obj->is_approved;
        $this->sort_order = $obj->sort_order;
        $this->created_at = $obj->created_at;
        $this->exported_at = $obj->exported_at;
        $this->qb_id = $obj->qb_id;
        $this->error_result = $obj->error_result;

        $this->save();
    }
}
