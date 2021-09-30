<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixVaryingStringsOnLeadLogs extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lead_logs', function (Blueprint $table) {
            $table->string('first_name', 255)->change();
            $table->string('last_name', 255)->change();
            $table->string('email_address', 255)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lead_logs', function (Blueprint $table) {
            $table->string('first_name', 250)->change();
            $table->string('last_name', 250)->change();
            $table->string('email_address', 250)->change();
        });
    }
}
