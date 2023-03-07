<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddSimpleFeatureFlagTable extends Migration
{
    public function up(): void
    {
        // @todo in near future we could relate the dealers with the enabled features
        Schema::create('simple_feature_flag', static function (Blueprint $table) {
            $table->char('code', 32)->primary();
            $table->boolean('is_enabled')->default(false)->index('feature_flag_index_is_enabled');
            $table->timestamp('created_at')->useCurrent();
        });

        DB::table('simple_feature_flag')->insert([
            ['code' => 'inventory-sdk', 'is_enabled' => false],
            ['code' => 'inventory-sdk-cache', 'is_enabled' => false]
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('simple_feature_flag');
    }
}
