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
            $table->integer('fbapp_page_id')->unique();
            $table->bigInteger('business_user_id')->index();
            $table->bigInteger('catalog_id')->index();
            $table->bigInteger('account_id')->index();
            $table->string('account_name');
            $table->bigInteger('feed_id')->index()->nullable();
            $table->text('filters');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->index(['dealer_id', 'dealer_location_id']);
        });

        // Update Integration Token Relation Type
        DB::statement("ALTER TABLE integration_token MODIFY COLUMN relation_type ENUM('" . implode("', '", array_keys(AccessToken::RELATION_TYPES)) . "')");


        // FB App Pages Exists?
        if (Schema::hasTable('fbapp_pages')) {
            // Add Timestamps and Drop Unnecessary Columns
            Schema::table('fbapp_pages', function (Blueprint $table) {
                // Make Big Integer
                $table->bigInteger('page_id')->change();

                // Add Timestamps
                $table->timestamps();

                // Drop Columns
                $table->dropColumn('access_token');
                $table->dropColumn('expires_at');
                $table->dropColumn('is_active');
                $table->dropColumn('is_auto');
            });

            // Update Integration Token Relation Type
            DB::statement("UPDATE fbapp_pages SET `created_at` = `timestamp`, `updated_at` = `timestamp`");
        } else {
            // Create it!
            Schema::create('fbapp_catalog', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('dealer_id')->index();
                $table->bigInteger('page_id')->index();
                $table->timestamp('timestamp')->useCurrent();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fbapp_catalog');

        Schema::table('fbapp_pages', function (Blueprint $table) {
            // Drop Timestamps
            $table->dropTimestamps();
        });
    }
}
