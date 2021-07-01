<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Bulk\Blog\BulkPostUpload;

class CreateBlogBulkUpload extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('blog_bulk_upload', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('dealer_id')->unsigned();
            $table->enum('status', [BulkPostUpload::VALIDATION_ERROR, BulkPostUpload::PROCESSING, BulkPostUpload::COMPLETE])->default(BulkPostUpload::PROCESSING);
            $table->text('import_source');
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
        Schema::dropIfExists('blog_bulk_upload');
    }
}
