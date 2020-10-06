<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIntegrationAccessToken extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('integration_token', function (Blueprint $table) {
            $table->increments('id'); // int(11) NOT NULL auto_increment,
            $table->integer('dealer_id'); // int(11) NOT NULL,
            $table->enum('relation_type', AccessToken::ACCESS_TOKEN_TYPES); // enum() NOT NULL,
            $table->integer('relation_id'); // int(11) NOT NULL,
            $table->string('access_token');
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
        Schema::dropIfExists('integration_token_perms');

        Schema::dropIfExists('integration_token');
    }
}
