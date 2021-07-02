<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDmsCvrCreds extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('dms_cvr_creds', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('dealer_id');
            $table->string('cvr_username')->default('TrailerCentral');
            $table->string('cvr_unique_id');
            $table->string('cvr_password', 1000);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('dms_cvr_creds');
    }
}
