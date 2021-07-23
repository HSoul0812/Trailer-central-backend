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
            // Set Nullable on Additional Fields
            $table->integer('expires_in')->nullable()->change();
            $table->timestamp('expires_at')->nullable()->change();
            $table->timestamp('issued_at')->nullable()->change();

            // Add New Indexes
            $table->index(['relation_type', 'relation_id']);
            $table->index(['access_token']);
        });

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
        //
    }
}
