<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddOneBedroomToFeaturesDisplay extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("UPDATE
	`inventory_feature_list`
SET
	`available_options` = concat(`available_options`, ', One Bedroom')
WHERE
	`feature_name` = 'Floor Plans'
	AND `show_in_only` = 'rv'
	AND `available_options` NOT LIKE '%, One Bedroom%';");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("UPDATE
	`inventory_feature_list`
SET
	`available_options` = REPLACE(`available_options`, ', One Bedroom', '')
WHERE
	`feature_name` = 'Floor Plans'
	AND `show_in_only` = 'rv'
	AND `available_options` LIKE '%, One Bedroom%';");
    }
}
