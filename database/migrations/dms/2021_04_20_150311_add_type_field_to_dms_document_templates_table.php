<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeFieldToDmsDocumentTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dms_document_templates', function (Blueprint $table) {
            $table->enum('type', ['quote', 'service'])->after('dealer_id')->default('quote');
            $table->enum('type_service', ['yes', 'no'])->default('no');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dms_document_templates', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->dropColumn('type_service');
        });
    }
}
