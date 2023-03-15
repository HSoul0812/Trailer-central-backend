<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateEnumApiEntityReference extends Migration
{
    /**
     * Run the migrations.
     * Add Bigtex Enum to options for api key column
     * @return void
     */
    public function up(): void
    {
        \DB::statement("ALTER TABLE `api_entity_reference` MODIFY COLUMN `api_key` ENUM('lt','pj','utc','lgs','novae','test','lamar','norstar','bigtex');");
    }
}
