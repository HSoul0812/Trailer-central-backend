<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateFileToDropTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('storage_object_to_drop', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('url', 350)->index('storage_object_to_drop_url_index');
            $table->timestamp('created_at')
                ->useCurrent()
                ->nullable()
                ->index('storage_object_to_drop_created_at_index');
            $table->timestamp('dropped_at')
                ->nullable()
                ->index('storage_object_to_drop_dropped_at_index');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('storage_object_to_drop');
    }
}
