<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use App\Models\FeatureFlag;

class AddInventorySdkKeywordWildcardFeature extends Migration
{
    public function up(): void
    {
        Schema::table('simple_feature_flag', static function (Blueprint $table) {
            $table->string('code', 50)->change();
        });

        FeatureFlag::create(['code' => 'inventory-sdk-es-keyword-wildcard', 'is_enabled' => true]);
    }

    /**
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException  when there is not a record to be deleted
     */
    public function down(): void
    {
        FeatureFlag::findOrFail('inventory-sdk-es-keyword-wildcard')->delete();
    }
}
