<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateInteractionIntegrationPermissionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('interaction_integration_permission', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('integration_id');
            $table->string('feature');
            $table->string('permission_level');
            $table->timestamps();
        });

        Schema::table('interaction_integration_permission', function (Blueprint $table) {
            $table->foreign('integration_id')
                ->references('id')
                ->on('interaction_integration')
                ->onDelete('CASCADE')
                ->onUpdate('CASCADE');
        });

        $twilio = DB::table('interaction_integration')->where(['name' => 'twilio',])->first(['id']);

        DB::table('interaction_integration_permission')->insert([
            'integration_id' => $twilio->id,
            'feature' => 'dealer_texts',
            'permission_level' => 'can_see_and_change',
            'created_at' => new \DateTime(),
            'updated_at' => new \DateTime(),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('interaction_integration_permission');
    }
}
