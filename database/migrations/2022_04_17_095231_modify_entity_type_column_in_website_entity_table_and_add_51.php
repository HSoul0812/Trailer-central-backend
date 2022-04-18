<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ModifyEntityTypeColumnInWebsiteEntityTableAndAdd51 extends Migration
{
        /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement(
            "ALTER TABLE
                      `website_entity`
                   MODIFY COLUMN
                      `entity_type` enum('1','2','3','5','6','7','8','9','11','12','15','16','910','25','26','27','28','29','30','31','32','33','34','35','36','37','41','42','43','44','50','51') DEFAULT NULL;"
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement(
            "ALTER TABLE
                      `website_entity`
                   MODIFY COLUMN
                      `entity_type` enum('1','2','3','5','6','7','8','9','11','12','15','16','910','25','26','27','28','29','30','31','32','33','34','35','36','37','41','42','43','44','50') DEFAULT NULL;"
        );
    }
}
