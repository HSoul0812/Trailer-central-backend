<?php

use Illuminate\Database\Migrations\Migration;

class FixFilenameOriginalImage extends Migration
{
    public function up(): void
    {
        // nothing to do

        // this up method has been updated after it has reached staging, so to do not break something it will remains
        // but its changes was avoided due they take time, and a later migration will run a definitive data change
        // also `inventory:fix-image-overlay-urls` could be called under demand to perform this change any time
    }

    public function down(): void
    {
        // nothing to do
    }
}
