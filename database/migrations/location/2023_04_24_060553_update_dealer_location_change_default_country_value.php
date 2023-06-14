<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateDealerLocationChangeDefaultCountryValue extends Migration
{
    public function __construct()
    {
        // to avoid ENUM migration error
        // @see https://stackoverflow.com/questions/33140860/laravel-5-1-unknown-database-type-enum-requested
        DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //change default value to USA to be consistent with geolocation
        Schema::table('dealer_location', function (Blueprint $table) {
            if (Schema::hasColumn('dealer_location', 'country')) {
                $table->string('country', 3)->default('USA')->change();
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
        Schema::table('dealer_location', function (Blueprint $table) {
            if (Schema::hasColumn('dealer_location', 'country')) {
                $table->string('country', 3)->default('US')->change();
            }
        });
    }
}
