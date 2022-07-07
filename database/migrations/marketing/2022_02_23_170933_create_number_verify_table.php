<?php

use App\Models\CRM\Text\NumberVerify;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNumberVerifyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dealer_texts_verify', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('dealer_number', 12)->index();
            $table->string('twilio_number', 12);
            $table->enum('verify_type', array_keys(NumberVerify::VERIFY_TYPES))->index();
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
        Schema::dropIfExists('dealer_texts_verify');
    }
}
