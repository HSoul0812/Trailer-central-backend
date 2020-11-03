<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Integration\Auth\AccessToken;

class CreateFbappCatalogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fbapp_catalog', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('dealer_id')->index();
            $table->integer('dealer_location_id')->index();
            $table->bigInteger('account_id')->index();
            $table->string('account_name');
            $table->bigInteger('page_id')->unique();
            $table->string('page_title');
            $table->text('filters');
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_scheduled')->default(false);
            $table->timestamps();

            $table->index(['dealer_id', 'dealer_location_id']);
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
        Schema::dropIfExists('fbapp_catalog');
    }
}
