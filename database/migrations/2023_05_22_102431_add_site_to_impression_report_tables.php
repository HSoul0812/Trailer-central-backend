<?php

use App\Domains\UserTracking\Actions\GetPageNameFromUrlAction;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSiteToImpressionReportTables extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('monthly_impression_reports', function (Blueprint $table) {
            $table->dropIndex(['year', 'month']);
            $table->dropIndex(['year', 'month', 'dealer_id']);
            $table->dropUnique(['year', 'month', 'inventory_id']);

            $table->string('site', 10)->default(GetPageNameFromUrlAction::SITE_TT_AF);

            $table->index(['site', 'year', 'month']);
            $table->index(['site', 'year', 'month', 'dealer_id']);
            $table->unique(['site', 'year', 'month', 'inventory_id']);
        });

        Schema::table('monthly_impression_countings', function (Blueprint $table) {
            $table->dropIndex(['year', 'month']);
            $table->dropUnique(['year', 'month', 'dealer_id']);
            $table->dropIndex(['dealer_id']);

            $table->string('site', 10)->default(GetPageNameFromUrlAction::SITE_TT_AF);

            $table->index(['site', 'year', 'month']);
            $table->unique(['site', 'year', 'month', 'dealer_id']);
            $table->index(['site', 'dealer_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monthly_impression_reports', function (Blueprint $table) {
            $table->dropIndex(['site', 'year', 'month']);
            $table->dropIndex(['site', 'year', 'month', 'dealer_id']);
            $table->dropUnique(['site', 'year', 'month', 'inventory_id']);

            $table->dropColumn('site');

            $table->index(['year', 'month']);
            $table->index(['year', 'month', 'dealer_id']);
            $table->unique(['year', 'month', 'inventory_id']);
        });

        Schema::table('monthly_impression_countings', function (Blueprint $table) {
            $table->dropIndex(['site', 'year', 'month']);
            $table->dropUnique(['site', 'year', 'month', 'dealer_id']);
            $table->dropIndex(['site', 'dealer_id']);

            $table->dropColumn('site');

            $table->index(['year', 'month']);
            $table->unique(['year', 'month', 'dealer_id']);
            $table->index(['dealer_id']);
        });
    }
}
