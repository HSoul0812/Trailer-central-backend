<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSsnRemovedColumnToWebsiteFormSubmissionsTable extends Migration
{
    private $tableName = 'website_form_submissions';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            $table->boolean('is_ssn_removed')->default(false);
            $table->index(['created_at', 'is_ssn_removed']);
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
            $table->dropColumn('is_ssn_removed');
            $table->dropIndex(['created_at', 'is_ssn_removed']);
        });
    }
}
