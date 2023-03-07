<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddBrandIndexToShowroom extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('showroom', static function (Blueprint $table) {
            $table->index(['brand'], 'brand_showroom_full_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('showroom', static function (Blueprint $table) {
            $table->dropIndex('brand_showroom_full_index');
        });
    }
}
