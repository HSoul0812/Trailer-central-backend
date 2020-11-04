<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPartOrdersBillingAddress extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('part_orders', function (Blueprint $table) {
            $table->string('shipto_city', 50)->nullable()->after('shipto_address');
            $table->string('shipto_region', 20)->nullable()->after('shipto_city');
            $table->string('shipto_postal', 10)->nullable()->after('shipto_region');
            $table->string('shipto_country')->nullable()->after('shipto_postal');
            $table->string('billto_name')->nullable()->after('shipto_country');
            $table->string('billto_address')->nullable()->after('billto_name');
            $table->string('billto_city', 50)->nullable()->after('billto_address');
            $table->string('billto_region', 20)->nullable()->after('billto_city');
            $table->string('billto_postal', 10)->nullable()->after('billto_region');
            $table->string('billto_country')->nullable()->after('billto_postal');
        });

        DB::statement('ALTER TABLE part_orders CHANGE shipto_address shipto_address VARCHAR(255)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('part_orders', function (Blueprint $table) {
            
        });
    }
}
