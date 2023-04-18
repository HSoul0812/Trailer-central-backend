<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Database\traits\WithIndexes;
class AddIndexesToFbappListingsTable extends Migration
{
    use WithIndexes;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!$this->indexExists('fbapp_listings', 'idx_inventory_id')) {
            Schema::table('fbapp_listings', function (Blueprint $table) {
                $table->index('inventory_id', 'idx_inventory_id');
            });
        }
        if (!$this->indexExists('fbapp_listings', 'idx_marketplace_id')) {
            Schema::table('fbapp_listings', function (Blueprint $table) {
                $table->index('marketplace_id', 'idx_marketplace_id');
            });
        }
        if (!$this->indexExists('fbapp_listings', 'idx_marketplace_id_created_at')) {
            Schema::table('fbapp_listings', function (Blueprint $table) {
                $table->index(['marketplace_id', 'created_at'], 'idx_marketplace_id_created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->dropIndexIfExist('fbapp_listings', 'idx_inventory_id');
        $this->dropIndexIfExist('fbapp_listings', 'idx_marketplace_id');
        $this->dropIndexIfExist('fbapp_listings', 'idx_created_at');
    }
}
