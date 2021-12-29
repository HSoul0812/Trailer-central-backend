<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddBigTexExportedLeadsColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('website_lead', function (Blueprint $table) {
            $table->boolean('bigtex_exported')->default(0);
            $table->index('bigtex_exported');
        });  
        
        DB::statement("UPDATE website_lead SET bigtex_exported = 1 WHERE 1");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('website_lead', function (Blueprint $table) {
            $table->dropColumn('bigtex_exported');
        });
    }
}
