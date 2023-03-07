<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PartAttributeRelationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('textrail_part_attributes', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->bigInteger('attribute_id')->unsigned();
            $table->foreign('attribute_id')
                ->references('id')
                ->on('textrail_attributes');

            $table->bigInteger('part_id')->unsigned();
            $table->foreign('part_id')
                ->references('id')
                ->on('textrail_parts');

            $table->string('attribute_value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('textrail_part_attributes');
    }
}
