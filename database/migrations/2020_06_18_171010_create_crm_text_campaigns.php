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
        Schema::create('crm_text_template', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->integer('user_id');

            $table->string('name');

            $table->string('template');

            $table->timestamps();

            $table->tinyInteger('deleted');
        });

        Schema::create('crm_text_campaign', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->integer('user_id');

            $table->integer('template_id');

            $table->string('campain_name');

            $table->string('campain_subject');

            $table->string('from_email_address');

            $table->enum('action', Campaign::STATUS_ACTIONS);

            $table->integer('location_id');

            $table->integer('send_after_days');

            $table->integer('unit_category');

            $table->enum('include_archived', Campaign::STATUS_ARCHIVED);

            $table->tinyInteger('is_enabled');

            $table->timestamps();

            $table->tinyInteger('deleted');
        });

        Schema::create('crm_text_campaign_sent', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->integer('text_campaign_id');

            $table->integer('lead_id');

            $table->integer('text_id');

            $table->timestamps();

            $table->tinyInteger('deleted');
        });

        Schema::create('crm_text_blast', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->integer('user_id');

            $table->integer('template_id');

            $table->string('campain_name');

            $table->string('campain_subject');

            $table->string('from_email_address');

            $table->enum('action', Blast::STATUS_ACTIONS);

            $table->integer('location_id');

            $table->integer('send_after_days');

            $table->integer('unit_category');

            $table->enum('include_archived', Blast::STATUS_ARCHIVED);

            $table->tinyInteger('is_delivered');

            $table->tinyInteger('is_cancelled');

            $table->timestamps();

            $table->tinyInteger('deleted');
        });

        Schema::create('crm_text_blast_sent', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->integer('text_blast_id');

            $table->integer('lead_id');

            $table->integer('text_id');

            $table->timestamps();

            $table->tinyInteger('deleted');
        });

        Schema::create('crm_text_stop', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->integer('lead_id');

            $table->integer('text_id');

            $table->integer('response_id');

            $table->string('text_number');

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
        Schema::dropIfExists('crm_text_template');

        Schema::dropIfExists('crm_text_campaign');

        Schema::dropIfExists('crm_text_campaign_sent');

        Schema::dropIfExists('crm_text_blast');

        Schema::dropIfExists('crm_text_blast_sent');

        Schema::dropIfExists('crm_text_stop');
    }
}
