<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Database\traits\WithIndexes;

class AddFilenameNoverlayIndexOnImageTable extends Migration
{
    use WithIndexes;

    public function up(): void
    {
        Schema::table('image', function (Blueprint $table) {
            if (!$this->indexExists('image', 'idx_image_filename_noverlay')) {
                $table->index('filename_noverlay', 'idx_image_filename_noverlay');
            }
        });
    }

    public function down(): void
    {
        $this->dropIndexIfExist('image', 'idx_image_filename_noverlay');
    }
}
