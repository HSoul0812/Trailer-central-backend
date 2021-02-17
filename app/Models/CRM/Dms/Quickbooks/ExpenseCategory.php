<?php

namespace App\Models\CRM\Dms\Quickbooks;

use Illuminate\Database\Eloquent\Model;

/**
 * @author Marcel
 */
class ExpenseCategory extends Model
{
    protected $table = 'qb_expense_categories';

    public $timestamps = false;

    protected $guarded = [];

    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
