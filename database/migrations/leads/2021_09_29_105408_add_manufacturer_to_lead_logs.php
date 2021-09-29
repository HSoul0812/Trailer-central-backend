<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddManufacturerToLeadLogs extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lead_logs', function (Blueprint $table) {
            $table->string('brand', 80)
                ->nullable()
                ->index('lead_logs_i_brand')
                ->after('email_address');
            $table->string('manufacturer', 80)
                ->index('lead_logs_i_manufacturer')
                ->after('email_address');
            $table->string('vin', 40)
                ->nullable()
                ->index('lead_logs_i_vin')
                ->after('email_address');
        });

        DB::statement('CREATE INDEX lead_logs_igin_manufacturer ON lead_logs USING gin (manufacturer gin_trgm_ops)');
        DB::statement('CREATE INDEX lead_logs_igin_brand ON lead_logs USING gin (brand gin_trgm_ops)');
        DB::statement('CREATE INDEX lead_logs_igin_vin ON lead_logs USING gin (vin gin_trgm_ops)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lead_logs', function (Blueprint $table) {
            $table->dropColumn('brand');
            $table->dropColumn('manufacturer');
            $table->dropColumn('vin');
        });
    }
}
