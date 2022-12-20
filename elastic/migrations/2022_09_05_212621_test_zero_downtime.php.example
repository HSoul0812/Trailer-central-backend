<?php
declare(strict_types=1);

use ElasticMigrations\MigrationInterface;
use App\Models\Inventory\Inventory;
use Laravel\Scout\EngineManager;


final class TestZeroDowntime implements MigrationInterface
{
    /**
     * Run the migration.
     */
    public function up(): void
    {
        $engine = app(EngineManager::class);

        $engine->safeSyncImporter(new Inventory, 'inventory_v2');
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        //
    }
}
