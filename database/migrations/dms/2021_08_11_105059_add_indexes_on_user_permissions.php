<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesOnUserPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('dealer_user_permissions', function (Blueprint $table) {

            $table->index(['dealer_user_id'], 'dealer_user_permissions_lookup_dealer_user');
            $table->index(['dealer_user_id', 'feature'], 'dealer_user_permissions_lookup_dealer_user_feature');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('dealer_user_permissions', function (Blueprint $table) {

            $table->dropIndex('dealer_user_permissions_lookup_dealer_user');
            $table->dropIndex('dealer_user_permissions_lookup_dealer_user_feature');
        });
    }
}
