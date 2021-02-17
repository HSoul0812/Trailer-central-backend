<?php

namespace App\Models\CRM\Dms\Quickbooks;

use Illuminate\Database\Eloquent\Model;

/**
 * @author Marcel
 */
class Expense extends Model
{
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

    const RELATED_TABLES = [
        self::TABLE_INVOICE,
        self::TABLE_POS_SALE,
        self::TABLE_REPAIR_ORDER,
        self::TABLE_FLOORPLAN_PAYMENT,
    ];

    protected $table = 'qb_expenses';

    public $timestamps = false;

    protected $guarded = ['qb_id'];

    public function categories()
    {
        return $this->hasMany(ExpenseCategory::class);
    }

    public function items()
    {
        return $this->hasMany(ExpenseItem::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }
}
