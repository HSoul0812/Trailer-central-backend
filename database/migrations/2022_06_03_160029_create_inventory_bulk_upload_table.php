<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Bulk\Inventory\BulkUpload;

class CreateInventoryBulkUploadTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory_bulk_upload', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('dealer_id')->unsigned();
            $table->enum('status', [BulkUpload::VALIDATION_ERROR, BulkUpload::PROCESSING, BulkUpload::COMPLETE])->default(BulkUpload::PROCESSING);
            $table->text('import_source');
            $table->text('validation_errors');

            $table->timestamps();

            $table->foreign('dealer_id')
                ->references('dealer_id')
                ->on('dealer')
                ->onDelete('CASCADE')
                ->onUpdate('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inventory_bulk_upload');
    }
}
