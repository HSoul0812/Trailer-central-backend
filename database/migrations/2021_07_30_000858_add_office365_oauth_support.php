<?php

use App\Models\Integration\Auth\AccessToken;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddOffice365OauthSupport extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('integration_token', function (Blueprint $table) {
            // Append Access Token
            $table->string('state')->after('relation_id')->nullable()->index();

            // Add New Indexes
            $table->index(['relation_type', 'relation_id']);
        });

        // Update Integration Token Expires In/Expires At/Issued At
        DB::statement("ALTER TABLE integration_token MODIFY COLUMN access_token TEXT NULL");
        DB::statement("ALTER TABLE integration_token MODIFY COLUMN expires_in INT(11) NULL");
        DB::statement("ALTER TABLE integration_token MODIFY COLUMN expires_at TIMESTAMP NULL");
        DB::statement("ALTER TABLE integration_token MODIFY COLUMN expires_at TIMESTAMP NULL");

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
        //
    }
}
