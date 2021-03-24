<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWarrantyCustomerIdToDmsRepairOrder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dms_repair_order', function (Blueprint $table) {
            $table->integer('warranty_customer_id')->nullable();
            $table->index('warranty_customer_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dms_repair_order', function (Blueprint $table) {
            $table->dropColumn('warranty_customer_id');
        });
    }
} 
