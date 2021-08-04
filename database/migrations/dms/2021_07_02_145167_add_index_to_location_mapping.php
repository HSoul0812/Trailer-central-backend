<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexToLocationMapping extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('location_quickbooks_mapping', function (Blueprint $table) {

            $table->index(['dealer_location_id'], 'location_quickbooks_mapping_lookup_dealer_location_1');
            $table->index(['dealer_id', 'dealer_location_id'], 'location_quickbooks_mapping_lookup_dealer_location_2');
            $table->index(['dealer_id', 'quickbooks_id'], 'location_quickbooks_mapping_lookup_dealer_qbo_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('location_quickbooks_mapping', function (Blueprint $table) {

            $table->dropIndex('location_quickbooks_mapping_lookup_dealer_location_1');
            $table->dropIndex('location_quickbooks_mapping_lookup_dealer_location_2');
            $table->dropIndex('location_quickbooks_mapping_lookup_dealer_qbo_id');
        });
    }
}
