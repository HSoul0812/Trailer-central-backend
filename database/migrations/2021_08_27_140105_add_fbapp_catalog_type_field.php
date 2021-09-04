<?php

use App\Models\Integration\Facebook\Catalog;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFbappCatalogTypeField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fbapp_catalog', function (Blueprint $table) {
            $table->enum('catalog_type', Catalog::CATALOG_TYPES)->after('catalog_id')
                  ->default(Catalog::DEFAULT_TYPE)->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fbapp_catalog', function (Blueprint $table) {
            $table->dropColumn('catalog_type');
        });
    }
}
