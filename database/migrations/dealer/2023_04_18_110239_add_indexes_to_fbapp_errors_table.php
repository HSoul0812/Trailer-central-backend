<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Database\traits\WithIndexes;

class AddIndexesToFbappErrorsTable extends Migration
{
    use WithIndexes;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!$this->indexExists('fbapp_errors', 'idx_created_at')) {
            Schema::table('fbapp_errors', function (Blueprint $table) {
                $table->index('created_at', 'idx_created_at');
            });
        }

        if (!$this->indexExists('fbapp_errors', 'idx_marketplace_id_created_at')) {
            Schema::table('fbapp_errors', function (Blueprint $table) {
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
        $this->dropIndexIfExist('fbapp_errors', 'idx_created_at');
        $this->dropIndexIfExist('fbapp_errors', 'idx_marketplace_id_created_at');
    }
}
