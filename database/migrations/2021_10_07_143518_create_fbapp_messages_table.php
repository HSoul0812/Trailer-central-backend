<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFbappMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fbapp_messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('message_id', 100)->unique();
            $table->string('conversation_id', 25)->index();
            $table->integer('interaction_id')->index();
            $table->bigInteger('from_id');
            $table->bigInteger('to_id');
            $table->string('message');
            $table->string('tags');
            $table->tinyInteger('read');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fbapp_messages');
    }
}
