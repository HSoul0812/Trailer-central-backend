<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddIdsExportedLeadsColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('website_lead', function (Blueprint $table) {
            $table->boolean('ids_exported')->default(0);
            $table->index('ids_exported');
        });  
        
        DB::statement("UPDATE website_lead SET ids_exported = 1 WHERE 1");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('website_lead', function (Blueprint $table) {
            $table->dropColumn('ids_exported');
        });
    }
}
