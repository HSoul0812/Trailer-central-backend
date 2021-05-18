<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDmsOkidataFormsTable extends Migration
{
    const OKIDATA_FORM_DR2407 = [
        'name' => 'DR2407',
        'region' => 'CO',
        'description' => 'Dealer\'s Bill of Sales for a Motor Vehicle',
        'department' => 'Department of Revenue',
        'division' => 'Division of Motor Vehicles',
        'website' => 'www.revenue.state.co.us'
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dms_okidata_forms', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 25)->unique();
            $table->string('region', 3)->index();
            $table->string('description', 255)->comment('Title of the Form');
            $table->string('department', 255);
            $table->string('division', 255);
            $table->string('website', 255);
            $table->timestamps();
        });

        DB::table('dms_okidata_forms')->insert(self::OKIDATA_FORM_DR2407);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dms_okidata_forms');
    }
}
