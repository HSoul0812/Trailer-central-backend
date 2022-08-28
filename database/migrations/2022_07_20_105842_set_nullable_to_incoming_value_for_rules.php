<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SetNullableToIncomingValueForRules extends Migration
{
    /**
     * Construct
     */
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
        Schema::table('collector_specification_rules', function (Blueprint $table) {
            $table->string('value')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('collector_specification_rules', function (Blueprint $table) {
            $table->string('value')->nullable(false)->change();
        });
    }
}
