<?php

use App\Models\User\DealerLocationSalesTaxItem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDealerLocationSalesTaxItemV2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dealer_location_sales_tax_item_v2', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('dealer_location_id'); // int(11) NOT NULL,
            $table->integer('entity_type_id'); // int(10) DEFAULT NULL,
            $table->enum('item_type', DealerLocationSalesTaxItem::$types); // enum('state','county','city','district1','district2','district3','district4','dmv','registration') NOT NULL,
            $table->decimal('tax_pct', 6, 6); // decimal(6,6) NOT NULL DEFAULT '0.000000',
            $table->decimal('tax_cap', 10, 2); // decimal(10,2) DEFAULT NULL,
            $table->integer('standard'); // tinyint(1) NOT NULL,
            $table->integer('tax_exempt'); // tinyint(1) NOT NULL,
            $table->integer('out_of_state_reciprocal'); // tinyint(1) NOT NULL,
            $table->integer('out_of_state_non_reciprocal'); // tinyint(1) NOT NULL,
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dealer_location_sales_tax_item_v2');
    }
}
