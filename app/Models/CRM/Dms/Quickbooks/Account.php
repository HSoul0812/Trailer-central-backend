<?php

namespace App\Models\CRM\Dms\Quickbooks;

use Illuminate\Database\Eloquent\Model;

/**
 * @author Marcel
 */
class Account extends Model
{
    const TYPE_BANK = 'Bank';
    const TYPE_OTHER_CURRENT_ASSET = 'Other Current Asset';
    const TYPE_FIXED_ASSET = 'Fixed Asset';
    const TYPE_OTHER_ASSET = 'Other Asset';
    const TYPE_ACCOUNTS_RECEIVERABLE = 'Accounts Receivable';
    const TYPE_EQUITY = 'Equity';
    const TYPE_EXPENSE = 'Expense';
    const TYPE_OTHER_EXPENSE = 'Other Expense';
    const TYPE_COGS_SOLD = 'Cost of Goods Sold';
    const TYPE_ACCOUNTS_PAYABLE = 'Accounts Payable';
    const TYPE_CREDIT_CARD = 'Credit Card';
    const TYPE_LONG_TERM_LIABILITY = 'Long Term Liability';
    const TYPE_OTHER_CURRENT_LIABILITY = 'Other Current Liability';
    const TYPE_INCOME = 'Income';
    const TYPE_OTHER_INCOME = 'Other Income';
    
    const ACCOUNT_TYPES = [
        self::TYPE_BANK,
        self::TYPE_OTHER_CURRENT_ASSET,
        self::TYPE_FIXED_ASSET,
        self::TYPE_OTHER_ASSET,
        self::TYPE_ACCOUNTS_RECEIVERABLE,
        self::TYPE_EQUITY,
        self::TYPE_EXPENSE,
        self::TYPE_OTHER_EXPENSE,
        self::TYPE_COGS_SOLD,
        self::TYPE_ACCOUNTS_PAYABLE,
        self::TYPE_CREDIT_CARD,
        self::TYPE_LONG_TERM_LIABILITY,
        self::TYPE_OTHER_CURRENT_LIABILITY,
        self::TYPE_INCOME,
        self::TYPE_OTHER_INCOME,
    ];

    protected $table = 'qb_accounts';

    protected $guarded = ['qb_id'];

    public function parent()
    {
        return $this->hasOne(Account::class, 'id', 'parent_id');
    }
}
