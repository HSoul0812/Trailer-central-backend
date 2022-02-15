<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class SetTitleToDealerLocationQuoteFeeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('dealer_location_quote_fee')
            ->select('id', 'fee_type')
            ->where('title', '=', '')
            ->orderBy('id')
            ->chunk(500, function ($fees) {
                foreach ($fees as $fee) {
                    $title = ucfirst(implode(' ', explode('_', $fee->fee_type)));

                    DB::table('dealer_location_quote_fee')
                        ->where(['id' => $fee->id])
                        ->update(['title' => $title]);
                }
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
