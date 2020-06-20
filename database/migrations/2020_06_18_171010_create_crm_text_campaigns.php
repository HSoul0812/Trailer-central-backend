<?php

use App\Models\CRM\Text\Blast;
use App\Models\CRM\Text\Campaign;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCrmTextCampaigns extends Migration
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
            $table->bigIncrements('id');

            $table->integer('user_id');

            $table->string('name');

            $table->string('template');

            $table->timestamps();

            $table->tinyInteger('deleted')->default(0)->index();

            $table->index(['user_id', 'name']);
        });

        Schema::table('crm_text_template', function (Blueprint $table) {
            $table->foreign('user_id')
                    ->references('user_id')
                    ->on('new_user')
                    ->onDelete('CASCADE')
                    ->onUpdate('CASCADE');
        });


        // Create CRM Text Campaign
        Schema::create('crm_text_campaign', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->integer('user_id');

            $table->integer('template_id')->index();

            $table->string('campain_name');

            $table->string('campain_subject');

            $table->string('from_email_address')->index();

            $table->enum('action', Campaign::STATUS_ACTIONS)->nullable();

            $table->integer('location_id')->nullable();

            $table->integer('send_after_days')->nullable();

            $table->integer('unit_category')->nullable();

            $table->enum('include_archived', Campaign::STATUS_ARCHIVED)->default('0');

            $table->tinyInteger('is_enabled')->default(1)->index();

            $table->timestamps();

            $table->tinyInteger('deleted')->default(0)->index();

            $table->unique(['user_id', 'campaign_name']);
        });

        Schema::table('crm_text_campaign', function (Blueprint $table) {
            $table->foreign('user_id')
                    ->references('user_id')
                    ->on('new_user')
                    ->onDelete('CASCADE')
                    ->onUpdate('CASCADE');

            $table->foreign('template_id')
                    ->references('id')
                    ->on('crm_website_template');
        });


        // Create CRM Text Campaign Sent
        Schema::create('crm_text_campaign_sent', function (Blueprint $table) {
            $table->integer('text_campaign_id');

            $table->integer('lead_id');

            $table->integer('text_id')->index();

            $table->timestamps();

            $table->tinyInteger('deleted')->default(0)->index();

            $table->primary(['text_campaign_id', 'lead_id']);
        });

        Schema::table('crm_text_campaign_sent', function (Blueprint $table) {
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


        // Create CRM Text Blast
        Schema::create('crm_text_blast', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->integer('user_id');

            $table->integer('template_id')->index();

            $table->string('campain_name');

            $table->string('campain_subject');

            $table->string('from_email_address')->index();

            $table->enum('action', Blast::STATUS_ACTIONS)->nullable();

            $table->integer('location_id')->nullable();

            $table->integer('send_after_days')->nullable();

            $table->integer('unit_category')->nullable();

            $table->enum('include_archived', Blast::STATUS_ARCHIVED)->default('0');

            $table->tinyInteger('is_delivered')->default(0)->index();

            $table->tinyInteger('is_cancelled')->default(0)->index();

            $table->timestamps();

            $table->tinyInteger('deleted')->default(0)->index();

            $table->unique(['user_id', 'campaign_name']);
        });

        Schema::table('crm_text_blast', function (Blueprint $table) {
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
            $table->integer('text_blast_id');

            $table->integer('lead_id');

            $table->integer('text_id')->index();

            $table->timestamps();

            $table->tinyInteger('deleted')->default(0)->index();

            $table->primary(['text_blast_id', 'lead_id']);
        });

        Schema::table('crm_text_blast_sent', function (Blueprint $table) {
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


        // CRM Text Stop
        Schema::create('crm_text_stop', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->integer('lead_id');

            $table->integer('text_id');

            $table->integer('response_id');

            $table->tinyInteger('deleted')->default(0)->index();

            $table->timestamps();
        });

        Schema::table('crm_text_stop', function (Blueprint $table) {
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
        Schema::dropIfExists('crm_text_template');

        Schema::dropIfExists('crm_text_campaign');

        Schema::dropIfExists('crm_text_campaign_sent');

        Schema::dropIfExists('crm_text_blast');

        Schema::dropIfExists('crm_text_blast_sent');

        Schema::dropIfExists('crm_text_stop');
    }
}
