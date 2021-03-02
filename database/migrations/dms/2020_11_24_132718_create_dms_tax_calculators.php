<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDmsTaxCalculators extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dms_tax_calculators', function (Blueprint $table) {
            //
            $table->increments('id');
            $table->integer('dealer_id')->nullable();
            $table->string('title');
            $table->string('description')->nullable();
            $table->string('code');

            $table->timestamps();
        });

        $taxCalculator = new \App\Models\CRM\Dms\TaxCalculator();
        $taxCalculator->id = 1; // default for all dealers
        $taxCalculator->title = 'Default';
        $taxCalculator->description = 'Default tax calculator. Do not change unless advised.';
        $taxCalculator->code = 'default';
        $taxCalculator->save();

        $taxCalculator = new \App\Models\CRM\Dms\TaxCalculator();
        $taxCalculator->id = 2;
        $taxCalculator->title = 'CO Default';
        $taxCalculator->description = 'CO tax calculator. Out of state customers will have no state tax on deals.';
        $taxCalculator->code = 'co_default';
        $taxCalculator->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dms_tax_calculators');
    }
}
