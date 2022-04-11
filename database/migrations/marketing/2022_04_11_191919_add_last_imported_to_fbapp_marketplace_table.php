<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLastImportedToFbappMarketplaceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fbapp_marketplace', function (Blueprint $table) {
            $table->timestamp('imported_at')->nullable()->index()->after('tfa_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fbapp_marketplace', function (Blueprint $table) {
            $table->dropColumn('imported_at');
        });
    }
}
