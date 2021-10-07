<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFbappConversationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fbapp_conversations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('conversation_id')->unique();
            $table->bigInteger('page_id')->index();
            $table->bigInteger('user_id')->index();
            $table->string('link');
            $table->string('snippet');
            $table->timestamp('newest_update');
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
        Schema::dropIfExists('fbapp_conversations');
    }
}
