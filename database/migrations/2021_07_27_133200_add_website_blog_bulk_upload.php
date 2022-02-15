<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Bulk\Blog\BulkPostUpload;

class AddWebsiteBlogBulkUpload extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('blog_bulk_upload', function (Blueprint $table) {
            $table->integer('website_id')->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('blog_bulk_upload', function (Blueprint $table) {
            $table->dropColumn('website_id');
        });
    }
}
