<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddTrailerTraderRequiredIndexesForWebsiteLead extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('website_lead', function (Blueprint $table) {
            $table->index(['lead_type', 'is_spam'], 'website_lead_lead_type_is_spam_index');
        });

        DB::statement('create index website_lead_lead_type_date_submitted_is_spam_index
	                  on website_lead (lead_type asc, is_spam asc, date_submitted desc);');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('website_lead', function (Blueprint $table) {
            $table->dropIndex('website_lead_lead_type_is_spam_index');
            $table->dropIndex('website_lead_lead_type_is_spam_date_submitted_index');
        });
    }
}
