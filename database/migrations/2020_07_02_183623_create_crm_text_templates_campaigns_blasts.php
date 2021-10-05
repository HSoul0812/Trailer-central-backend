<?php

use App\Models\CRM\Text\Blast;
use App\Models\CRM\Text\Campaign;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCrmTextTemplatesCampaignsBlasts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create CRM Text Template
        Schema::create('crm_text_template', function (Blueprint $table) {
            $table->increments('id')->unsigned();

            $table->integer('user_id')->unsigned();

            $table->string('name');

            $table->string('template');

            $table->timestamps();

            $table->tinyInteger('deleted')->default(0)->index();

            $table->index(['user_id', 'name']);

            $table->foreign('user_id')
                    ->references('user_id')
                    ->on('new_user')
                    ->onDelete('CASCADE')
                    ->onUpdate('CASCADE');
        });


        // Create CRM Text Campaign
        Schema::create('crm_text_campaign', function (Blueprint $table) {
            $table->increments('id')->unsigned();

            $table->integer('user_id')->unsigned();

            $table->integer('template_id')->unsigned()->index();

            $table->string('campaign_name', 100);

            $table->string('campaign_subject');

            $table->string('from_sms_number')->index()->nullable();

            $table->enum('action', Campaign::STATUS_ACTIONS)->nullable();

            $table->integer('location_id')->nullable();

            $table->integer('send_after_days')->nullable();

            $table->enum('include_archived', Campaign::STATUS_ARCHIVED)->default('0');

            $table->tinyInteger('is_enabled')->default(1)->index();

            $table->timestamps();

            $table->tinyInteger('deleted')->default(0)->index();

            $table->unique(['user_id', 'campaign_name']);

            $table->foreign('user_id')
                    ->references('user_id')
                    ->on('new_user')
                    ->onDelete('CASCADE')
                    ->onUpdate('CASCADE');

            $table->foreign('template_id')
                    ->references('id')
                    ->on('crm_text_template');
        });

        // Create CRM Text Campaign Sent
        Schema::create('crm_text_campaign_sent', function (Blueprint $table) {
            $table->integer('text_campaign_id')->unsigned();

            $table->integer('lead_id')->unsigned();

            $table->integer('text_id')->index();

            $table->timestamps();

            $table->tinyInteger('deleted')->default(0)->index();

            $table->primary(['text_campaign_id', 'lead_id']);

            $table->foreign('text_campaign_id')
                    ->references('id')
                    ->on('crm_text_campaign');

            $table->foreign('lead_id')
                    ->references('identifier')
                    ->on('website_lead');

            $table->foreign('text_id')
                    ->references('id')
                    ->on('dealer_texts_log');
        });

        // Create CRM Text Campaign Brand
        Schema::create('crm_text_campaign_brand', function (Blueprint $table) {
            $table->increments('id')->unsigned();

            $table->integer('text_campaign_id')->unsigned()->index();

            $table->string('brand');

            $table->timestamps();

            $table->foreign('text_campaign_id')
                    ->references('id')
                    ->on('crm_text_campaign')
                    ->onDelete('CASCADE')
                    ->onUpdate('CASCADE');
        });

        // Create CRM Text Campaign Category
        Schema::create('crm_text_campaign_category', function (Blueprint $table) {
            $table->increments('id')->unsigned();

            $table->integer('text_campaign_id')->unsigned()->index();

            $table->string('category');

            $table->timestamps();

            $table->foreign('text_campaign_id')
                    ->references('id')
                    ->on('crm_text_campaign')
                    ->onDelete('CASCADE')
                    ->onUpdate('CASCADE');
        });


        // Create CRM Text Blast
        Schema::create('crm_text_blast', function (Blueprint $table) {
            $table->increments('id')->unsigned();

            $table->integer('user_id')->unsigned();

            $table->integer('template_id')->unsigned()->index();

            $table->string('campaign_name', 100);

            $table->string('campaign_subject');

            $table->string('from_sms_number')->index()->nullable();

            $table->enum('action', Blast::STATUS_ACTIONS)->nullable();

            $table->integer('location_id')->nullable();

            $table->integer('send_after_days')->nullable();

            $table->enum('include_archived', Blast::STATUS_ARCHIVED)->default('0');

            $table->tinyInteger('is_delivered')->default(0)->index();

            $table->tinyInteger('is_cancelled')->default(0)->index();

            $table->timestamp('send_date')->index();

            $table->timestamps();

            $table->tinyInteger('deleted')->default(0)->index();

            $table->unique(['user_id', 'campaign_name']);

            $table->foreign('user_id')
                    ->references('user_id')
                    ->on('new_user')
                    ->onDelete('CASCADE')
                    ->onUpdate('CASCADE');

            $table->foreign('template_id')
                    ->references('id')
                    ->on('crm_text_template');
        });

        // CRM Text Blast Sent
        Schema::create('crm_text_blast_sent', function (Blueprint $table) {
            $table->integer('text_blast_id')->unsigned();

            $table->integer('lead_id')->unsigned();

            $table->integer('text_id')->index();

            $table->timestamps();

            $table->tinyInteger('deleted')->default(0)->index();

            $table->primary(['text_blast_id', 'lead_id']);

            $table->foreign('text_blast_id')
                    ->references('id')
                    ->on('crm_text_blast');

            $table->foreign('lead_id')
                    ->references('identifier')
                    ->on('website_lead');

            $table->foreign('text_id')
                    ->references('id')
                    ->on('dealer_texts_log');
        });

        // Create CRM Text Blast Brand
        Schema::create('crm_text_blast_brand', function (Blueprint $table) {
            $table->increments('id')->unsigned();

            $table->integer('text_blast_id')->unsigned()->index();

            $table->string('brand');

            $table->timestamps();

            $table->foreign('text_blast_id')
                    ->references('id')
                    ->on('crm_text_blast')
                    ->onDelete('CASCADE')
                    ->onUpdate('CASCADE');
        });

        // Create CRM Text Blast Category
        Schema::create('crm_text_blast_category', function (Blueprint $table) {
            $table->increments('id')->unsigned();

            $table->integer('text_blast_id')->unsigned()->index();

            $table->string('category');

            $table->timestamps();

            $table->foreign('text_blast_id')
                    ->references('id')
                    ->on('crm_text_blast')
                    ->onDelete('CASCADE')
                    ->onUpdate('CASCADE');
        });


        // CRM Text Stop
        Schema::create('crm_text_stop', function (Blueprint $table) {
            $table->increments('id')->unsigned();

            $table->integer('lead_id')->unsigned();

            $table->integer('text_id');

            $table->integer('response_id');

            $table->tinyInteger('deleted')->default(0)->index();

            $table->timestamps();

            $table->foreign('lead_id')
                    ->references('identifier')
                    ->on('website_lead');

            $table->foreign('text_id')
                    ->references('id')
                    ->on('dealer_texts_log');

            $table->foreign('response_id')
                    ->references('id')
                    ->on('dealer_texts_log');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('crm_text_stop');

        Schema::dropIfExists('crm_text_blast_brand');

        Schema::dropIfExists('crm_text_blast_category');

        Schema::dropIfExists('crm_text_blast_sent');

        Schema::dropIfExists('crm_text_blast');

        Schema::dropIfExists('crm_text_campaign_brand');

        Schema::dropIfExists('crm_text_campaign_category');

        Schema::dropIfExists('crm_text_campaign_sent');

        Schema::dropIfExists('crm_text_campaign');

        Schema::dropIfExists('crm_text_template');
    }
}