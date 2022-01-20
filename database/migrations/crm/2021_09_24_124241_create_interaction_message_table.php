<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInteractionMessageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('interaction_message');

        Schema::create('interaction_message', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('message_type', ['email', 'sms', 'fb']);
            $table->unsignedInteger('tb_primary_id');
            $table->enum('tb_name', ['crm_email_history', 'dealer_texts_log']);
            $table->string('name')->nullable();
            $table->boolean('hidden')->default(false);
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
        Schema::dropIfExists('interaction_message');
    }
}
