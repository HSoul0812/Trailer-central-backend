<?php

use App\Models\Integration\Auth\AccessToken;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSystemEmailsTable extends Migration
{
    private const CUSTOM_EMAILS_INSERT = [
        'email' => 'adf@trailercentral.com',
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system_emails', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email')->unique();
            $table->timestamps();
        });

        DB::table('system_emails')->insert(self::CUSTOM_EMAILS_INSERT);

        // Update Integration Token Relation Type
        DB::statement("ALTER TABLE integration_token MODIFY COLUMN relation_type ENUM('" . implode("', '", array_keys(AccessToken::RELATION_TYPES)) . "')");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('system_emails');
    }
}
