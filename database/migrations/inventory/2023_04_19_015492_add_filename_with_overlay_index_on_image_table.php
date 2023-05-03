<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Database\traits\WithIndexes;

class AddFilenameWithOverlayIndexOnImageTable extends Migration
{
    use WithIndexes;

    public function up(): void
    {
        Schema::table('image', function (Blueprint $table) {
            if (!$this->indexExists('image', 'idx_image_filename_with_overlay')) {
                $table->index('filename_with_overlay', 'idx_image_filename_with_overlay');
            }
        });
    }

    public function down(): void
    {
        $this->dropIndexIfExist('image', 'idx_image_filename_with_overlay');
    }
}
