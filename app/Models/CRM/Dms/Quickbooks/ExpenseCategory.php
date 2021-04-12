<?php

namespace App\Models\CRM\Dms\Quickbooks;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @author Marcel
 */
class ExpenseCategory extends Model
{
    protected $table = 'qb_expense_categories';

    public $timestamps = false;

    protected $guarded = [];

    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
