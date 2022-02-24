<?php

use App\Models\Marketing\Facebook\Error;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFbappErrorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fbapp_errors', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('marketplace_id')->index();
            $table->integer('inventory_id')->nullable()->index();
            $table->string('action', 100)->nullable();
            $table->string('step', 100)->nullable();
            $table->enum('error_type', array_keys(Error::ERROR_TYPES))->index();
            $table->text('error_message');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fbapp_errors');
    }
}
