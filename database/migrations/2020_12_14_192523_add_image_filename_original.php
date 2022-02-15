<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddImageFilenameOriginal extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add Image Columns
        Schema::table('image', function (Blueprint $table) {
            $table->string('filename_original', 255)->nullable()->after('filename_noverlay');
            $table->string('source', 50)->nullable()->index()->after('filename_original');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop Image Columns
        Schema::table('image', function (Blueprint $table) {
            $table->dropColumn('filename_original');
            $table->dropColumn('source');
        });
    }
}
