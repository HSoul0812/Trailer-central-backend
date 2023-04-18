<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddIndexesToFbappErrorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fbapp_errors', function (Blueprint $table) {
            // Check if the index exists before attempting to create it
            $indexExists = DB::select(DB::raw("SHOW INDEX FROM fbapp_errors WHERE Key_name = 'idx_created_at'"));
            if (empty($indexExists)) {
                $table->index('created_at', 'idx_created_at');
            }

            $indexExists = DB::select(DB::raw("SHOW INDEX FROM fbapp_errors WHERE Key_name = 'idx_marketplace_id_created_at'"));
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
        Schema::table('fbapp_errors', function (Blueprint $table) {
            // Drop the indexes if they exist
            $indexExists = DB::select(DB::raw("SHOW INDEX FROM fbapp_errors WHERE Key_name = 'idx_created_at'"));
            if (!empty($indexExists)) {
                $table->dropIndex('idx_created_at');
            }

            $indexExists = DB::select(DB::raw("SHOW INDEX FROM fbapp_errors WHERE Key_name = 'idx_marketplace_id_created_at'"));
            if (!empty($indexExists)) {
                $table->dropIndex('idx_marketplace_id_created_at');
            }
        });
    }
}
