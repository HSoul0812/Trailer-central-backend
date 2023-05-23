<?php

use App\Models\AppToken;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppTokensTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('app_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('app_name');
            $table->string('token', AppToken::TOKEN_LENGTH);
            $table->timestamps();

            $table->unique(['app_name']);
            $table->unique(['token']);
        });

        AppToken::createWithAppName('TraderTrader');
        AppToken::createWithAppName('TrailerCentral API');
        AppToken::createWithAppName('Reporting');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_tokens');
    }
}
