<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebsiteUsersCacheTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('website_user_caches', function (Blueprint $table) {
            $table->id();
            $table->json('profile_data');
            $table->json('inventory_data');
            $table->timestamps();

            $table->foreignId('website_user_id')->unique()
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('website_users_cache');
    }
}
