<?php

namespace App\Models\CRM\Dms\Quickbooks;

use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @author Marcel
 */
class Expense extends Model
{
    use TableAware;

    // Entity Constants
    const ENTITY_VENDOR = 'Vendor';
    const ENTITY_CUSTOMER = 'Customer';
    const ENTITY_EMPLOYEE = 'Employee';
    
    const ENTITY_TYPES = [
        self::ENTITY_CUSTOMER,
        self::ENTITY_EMPLOYEE,
        self::ENTITY_VENDOR
    ];
    // Table constants
    const TABLE_INVOICE = 'qb_invoices';
    const TABLE_POS_SALE = 'crm_pos_sales';
    const TABLE_REPAIR_ORDER = 'dms_repair_order';
    const TABLE_FLOORPLAN_PAYMENT = 'inventory_floor_plan_payment';
    const TABLE_POS_REGISTER = 'crm_pos_register';
    const TABLE_PARTS_COST_HISTORY = 'parts_cost_history';
    const TABLE_DEALER_REFUNDS = 'dealer_refunds';

    const RELATED_TABLES = [
        self::TABLE_INVOICE,
        self::TABLE_POS_SALE,
        self::TABLE_REPAIR_ORDER,
        self::TABLE_FLOORPLAN_PAYMENT,
        self::TABLE_POS_REGISTER,
        self::TABLE_PARTS_COST_HISTORY,
    ];

    protected $table = 'qb_expenses';

    public $timestamps = false;

    protected $guarded = ['qb_id'];

    public function categories(): HasMany
    {
        return $this->hasMany(ExpenseCategory::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ExpenseItem::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * Get the possible tb_names values
     *
     * @return string[]
     */
    public static function tbNames(): array
    {
        return [
            self::TABLE_INVOICE,
            self::TABLE_POS_SALE,
            self::TABLE_REPAIR_ORDER,
            self::TABLE_FLOORPLAN_PAYMENT,
            self::TABLE_POS_REGISTER,
            self::TABLE_PARTS_COST_HISTORY,
            self::TABLE_DEALER_REFUNDS,
        ];
    }
}
