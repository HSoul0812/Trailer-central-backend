<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class WebsiteLeadAddMiddleName extends Migration
{
    private $tableName = 'website_lead';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            $table->string('middle_name', 50)->nullable()->default(null)
                ->after('first_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            $table->dropColumn(['middle_name']);
        });
    }
}
