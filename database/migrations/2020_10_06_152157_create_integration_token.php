<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Integration\Auth\AccessToken;

class CreateIntegrationToken extends Migration
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

            $table->integer('dealer_id')->index(); // int(11) NOT NULL,

            $table->enum('token_type', AccessToken::TOKEN_TYPES); // enum() NOT NULL,

            $table->enum('relation_type', AccessToken::RELATION_TYPES); // enum() NOT NULL,

            $table->integer('relation_id'); // int(11) NOT NULL,

            $table->string('access_token')->index(); // string(255) NOT NULL,

            $table->integer('id_token'); // int(11) NOT NULL,

            $table->integer('issued_at'); // datetime NOT NULL,

            $table->integer('expires_at'); // datetime NOT NULL,

            $table->timestamps();

            $table->unique(['token_type', 'relation_type', 'relation_id']);

            $table->index(['token_type', 'id_token']);
        });
        
        Schema::create('integration_token_scopes', function (Blueprint $table) {
            $table->increments('id'); // int(11) NOT NULL auto_increment,

            $table->integer('integration_token_id'); // int(11) NOT NULL,

            $table->string('scope', 50); // string(50) NOT NULL,

            $table->timestamps();

            $table->unique(['integration_token_id', 'scope']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('integration_token_scopes');

        Schema::dropIfExists('integration_token');
    }
}
