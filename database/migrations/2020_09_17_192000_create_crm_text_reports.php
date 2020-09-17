<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\CRM\Text\Stop;

class CreateCrmTextReports extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // CRM Text Stop
        Schema::create('crm_text_reports', function (Blueprint $table) {
            $table->increments('id')->unsigned();

            $table->integer('lead_id')->unsigned();

            $table->integer('text_id');

            $table->string('sms_number', 12)->index();

            $table->enum('type', Stop::REPORT_TYPES)->default(Stop::REPORT_TYPE_DEFAULT)->index();

            $table->timestamps();

            $table->index(['sms_number', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('crm_text_reports');
    }
}
