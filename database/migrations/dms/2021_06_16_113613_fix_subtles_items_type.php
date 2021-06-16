<?php

use App\Models\CRM\Dms\Quickbooks\Item;
use Illuminate\Database\Migrations\Migration;

class FixSubtlesItemsType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::transaction(function () {
            Item::where('name', '=', 'Sublet')->update(['type' => Item::ITEM_TYPES['PART']]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::transaction(function () {
            Item::where('name', '=', 'Sublet')->update(['type' => Item::ITEM_TYPES['ADD_ON']]);
        });
    }
}
