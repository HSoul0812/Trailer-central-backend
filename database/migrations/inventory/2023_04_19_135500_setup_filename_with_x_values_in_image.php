<?php

use Illuminate\Database\Migrations\Migration;
use App\Console\Commands\Inventory\FixInventoryImageOverlayUrls;

class SetupFilenameWithXValuesInImage extends Migration
{
    public function up(): void
    {
        $command = new FixInventoryImageOverlayUrls();
        $command->handle();
    }

    public function down(): void
    {
        // nothing to do
    }
}
