<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSpincarSettingsToCollectorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('collector', function (Blueprint $table) {
            $table->boolean('spincar_active')->default(false);
            $table->integer('spincar_spincar_id')->nullable();
            $table->text('spincar_filenames')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('collector', function (Blueprint $table) {
            $table->dropColumn('spincar_active');
            $table->dropColumn('spincar_spincar_id');
            $table->dropColumn('spincar_filenames');
        });
    }
}
