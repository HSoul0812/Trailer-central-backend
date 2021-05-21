<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePackagesInventoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('packages_inventory', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('inventory_id');
            $table->unsignedBigInteger('package_id');
            $table->boolean('is_main_item')->default(false);
            $table->timestamps();
        });

        Schema::table('packages_inventory', function (Blueprint $table) {
            $table->foreign('package_id')
                ->references('id')
                ->on('packages')
                ->onDelete('CASCADE')
                ->onUpdate('CASCADE');

            $table->foreign('inventory_id')
                ->references('inventory_id')
                ->on('inventory')
                ->onDelete('CASCADE')
                ->onUpdate('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('packages_inventory');
    }
}
