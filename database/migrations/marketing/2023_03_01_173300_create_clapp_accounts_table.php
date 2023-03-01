<?php

use App\Models\Integration\Auth\AccessToken;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClappAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clapp_accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('dealer_id')->index();
            $table->integer('profile_id')->nullable()->index();
            $table->integer('virtual_card_id')->nullable()->index();
            $table->string('username')->index();
            $table->string('password');
            $table->string('smtp_password');
            $table->string('smtp_server');
            $table->integer('smtp_port', 10);
            $table->string('smtp_security', 10);
            $table->string('smtp_auth');
            $table->string('imap_password');
            $table->string('imap_server');
            $table->integer('imap_port', 10);
            $table->string('imap_security', 10);
            $table->timestamps();
        });

        // Update Integration Token Relation Type
        DB::statement("ALTER TABLE integration_token MODIFY COLUMN token_type ENUM('" . implode("', '", array_keys(AccessToken::TOKEN_TYPES)) . "')");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clapp_accounts');
    }
}
