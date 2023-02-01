<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\FeatureFlag;

class AddInventorySdkCompression extends Migration
{
    public function up(): void
    {
        FeatureFlag::create(['code' => 'inventory-sdk-cache-compression', 'is_enabled' => false]);
    }

    /**
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException  when there is not a record to be deleted
     */
    public function down(): void
    {
        FeatureFlag::findOrFail('inventory-sdk-cache-compression')->delete();
    }
}
