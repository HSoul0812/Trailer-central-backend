<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddBismanIntegration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('INSERT INTO `integration`(`integration_id`, `code`, `module_name`, `module_status`, `name`, `description`, `domain`, `create_account_url`, `active`, `filters`, `frequency`, `last_run_at`, `settings`, `include_sold`, `send_email`, `uses_staging`) VALUES (65, "bisman","Bisman","idle","Bisman",NULL,"bismanonline.com","",1,"a:0:{}",21600,NULL,"a:0:{}",0,"",1);');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("DELETE FROM integration WHERE id = 65");
    }
}
