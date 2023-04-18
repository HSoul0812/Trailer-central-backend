<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesToFbappListingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fbapp_listings', function (Blueprint $table) {
            // Check if the index exists before attempting to create it
            $indexExists = DB::select(DB::raw("SHOW INDEX FROM fbapp_listings WHERE Key_name = 'idx_inventory_id'"));
            if (empty($indexExists)) {
                $table->index('inventory_id', 'idx_inventory_id');
            }

            $indexExists = DB::select(DB::raw("SHOW INDEX FROM fbapp_listings WHERE Key_name = 'idx_marketplace_id'"));
            if (empty($indexExists)) {
                $table->index('marketplace_id', 'idx_marketplace_id');
            }

            $indexExists = DB::select(DB::raw("SHOW INDEX FROM fbapp_listings WHERE Key_name = 'idx_created_at'"));
            if (empty($indexExists)) {
                $table->index('created_at', 'idx_created_at');
            }

            $indexExists = DB::select(DB::raw("SHOW INDEX FROM fbapp_listings WHERE Key_name = 'idx_marketplace_id_created_at'"));
            if (empty($indexExists)) {
                $table->index(['marketplace_id', 'created_at'], 'idx_marketplace_id_created_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fbapp_listings', function (Blueprint $table) {
            // Drop the indexes if they exist
            $indexExists = DB::select(DB::raw("SHOW INDEX FROM fbapp_listings WHERE Key_name = 'idx_inventory_id'"));
            if (!empty($indexExists)) {
                $table->dropIndex('idx_inventory_id');
            }

            $indexExists = DB::select(DB::raw("SHOW INDEX FROM fbapp_listings WHERE Key_name = 'idx_marketplace_id'"));
            if (!empty($indexExists)) {
                $table->dropIndex('idx_marketplace_id');
            }

            $indexExists = DB::select(DB::raw("SHOW INDEX FROM fbapp_listings WHERE Key_name = 'idx_created_at'"));
            if (!empty($indexExists)) {
                $table->dropIndex('idx_created_at');
            }

            $indexExists = DB::select(DB::raw("SHOW INDEX FROM fbapp_listings WHERE Key_name = 'idx_marketplace_id_created_at'"));
            if (!empty($indexExists)) {
                $table->dropIndex('idx_marketplace_id_created_at');
            }
        });
    }
}
