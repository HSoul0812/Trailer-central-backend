<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCountyAndStateTaxToUnitSale extends Migration
{
    /**
     * Instead of adding pos sales to crm_pos_sales, we will create a new invoice for pos sales.
     * So need to add some fields to invoice table for POS Sales (sales_person_id, discount, shipping)
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dms_unit_sale', function (Blueprint $table) {
            $table->decimal('state_tax')->nullable()->after('lien_license');
            $table->decimal('county_tax')->nullable()->after('state_tax');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('qb_invoices', function (Blueprint $table) {
            $table->dropColumn(['state_tax', 'county_tax']);
        });
    }
}
