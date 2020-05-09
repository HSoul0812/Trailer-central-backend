<?php

use App\Models\Bulk\Parts\BulkDownload;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartsBulkDownloadTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parts_bulk_download', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->integer('dealer_id')->unsigned();

            $table->enum('status', [
                BulkDownload::STATUS_NEW,
                BulkDownload::STATUS_PROCESSING,
                BulkDownload::STATUS_COMPLETED,
                BulkDownload::STATUS_ERROR,
            ])->default(BulkDownload::STATUS_NEW);

            $table->string('token');

            $table->string('export_file');

            $table->string('progress')->nullable();

            $table->text('result')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('parts_bulk_download');
    }
}
