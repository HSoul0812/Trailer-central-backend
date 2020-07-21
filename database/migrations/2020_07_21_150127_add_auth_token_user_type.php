<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User\AuthToken;

class CreateAuthTokenDealerUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('auth_token', function (Blueprint $table) {
            $table->enum('user_type', AuthToken::USER_TYPES)->default('dealer');

            $table->dropForeign('auth_token_user_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('auth_token', function (Blueprint $table) {
            $table->dropColumn('user_type');

            $table->foreign('user_id')
                    ->references('dealer_id')
                    ->on('dealer');
        });
    }
}
