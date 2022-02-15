<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateInterestPaidAccount extends Seeder
{
    /**
     * Do not make "Interest Paid" account to be a sub account (Cost of Goods Sold)
     * 
     * @return void
     */
    public function run()
    {
        DB::table('qb_accounts')
            ->where([
                ['name', '=', 'Interest Paid'],
                ['type', '=', 'Expense'],
                ['sub_type', '=', 'InterestPaid']
            ])
            ->update(['sub_account' => 0, 'parent_id' => null]);
    }
}
