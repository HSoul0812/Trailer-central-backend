<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HideDownPaymentItem extends Seeder
{
    /**
     * Set `hide` column to 1 for a qb_items_new row called "Down Payment"
     * 
     * @return void
     */
    public function run()
    {
        DB::table('qb_items_new')
            ->where('name', '=', 'Down Payment')
            ->update(['hide' => 1]);
    }
}
