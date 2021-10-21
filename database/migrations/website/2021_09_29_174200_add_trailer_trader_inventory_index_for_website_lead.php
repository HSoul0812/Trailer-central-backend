<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTrailerTraderInventoryIndexForWebsiteLead extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('website_lead', function (Blueprint $table) {
            $table->index(['inventory_id'], 'website_lead_inventory_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('website_lead', function (Blueprint $table) {
            $table->dropIndex('website_lead_inventory_index');
        });
    }
}
