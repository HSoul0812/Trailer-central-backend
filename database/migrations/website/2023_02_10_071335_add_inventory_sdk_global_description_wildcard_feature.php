<?php

use App\Models\FeatureFlag;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AddInventorySdkGlobalDescriptionWildcardFeature extends Migration
{
    public function up(): void
    {
        FeatureFlag::create(['code' => 'inventory-sdk-global-description-wildcard', 'is_enabled' => true]);
    }

    /**
     * @throws ModelNotFoundException  when there is not a record to be deleted
     * @throws Exception
     */
    public function down(): void
    {
        FeatureFlag::findOrFail('inventory-sdk-global-description-wildcard')->delete();
    }
}
