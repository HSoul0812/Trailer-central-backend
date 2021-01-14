<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SupportLeadEmailImportFormat extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add Import Format
        Schema::table('lead_email', function (Blueprint $table) {
            $table->integer('import_format')->nullable()->after('export_format');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop Import Format
        Schema::table('lead_email', function (Blueprint $table) {
            $table->dropColumn('import_format');
        });
    }
}
