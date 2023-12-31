<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDescriptionPartCategories extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('part_categories', function (Blueprint $table) {
            $table->text('description')->nullable()
          ->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('part_categories', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
}
