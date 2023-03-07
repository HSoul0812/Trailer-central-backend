<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewDescriptionFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inventory', function (Blueprint $table) {
            $table->text('description_old')->collation('utf8_general_ci')->nullable();
            $table->text('description_html_old')->collation('utf8_general_ci')->nullable();
        });

        \Illuminate\Support\Facades\DB::statement("UPDATE `inventory` SET `description_old` = `description` WHERE `description` IS NOT NULL AND `description` != ''");
        \Illuminate\Support\Facades\DB::statement("UPDATE `inventory` SET `description_html_old` = `description_html` WHERE `description_html` IS NOT NULL AND `description_html` != ''");
        \Illuminate\Support\Facades\DB::statement("UPDATE `inventory` SET `description_html` = `description` WHERE `description` IS NOT NULL AND `description` != ''");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inventory', function (Blueprint $table) {
            $table->dropColumn('description_old');
            $table->dropColumn('description_html_old');
        });
    }
}
