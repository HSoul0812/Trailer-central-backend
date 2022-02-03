<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Collection;

class AddCrmEmailHistoryIdColumnToCrmEmailBlastsSentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('crm_email_blasts_sent', function (Blueprint $table) {
            $table->integer('crm_email_history_id')->nullable();
        });

        Schema::table('crm_email_blasts_sent', function (Blueprint $table) {
            $table->foreign('crm_email_history_id')
                ->references('email_id')
                ->on('crm_email_history')
                ->onDelete('CASCADE')
                ->onUpdate('CASCADE');
        });

        DB::table('crm_email_history')
            ->select(['crm_email_history.email_id', 'crm_email_blasts_sent.email_blasts_id', 'crm_email_blasts_sent.lead_id'])
            ->join('crm_email_blasts_sent', 'crm_email_blasts_sent.message_id', '=', 'crm_email_history.message_id')
            ->where('crm_email_history.message_id', '!=', 0)
            ->where('crm_email_history.message_id', '!=', '')
            ->whereNotNull('crm_email_history.message_id')
            ->orderBy('crm_email_history.email_id')
            ->chunk(5000, function (Collection $data) {
                foreach ($data as $item) {
                    DB::table('crm_email_blasts_sent')->where([
                        'email_blasts_id' => $item->email_blasts_id,
                        'lead_id' => $item->lead_id,
                    ])->update(['crm_email_history_id' => $item->email_id]);
                }
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('crm_email_blasts_sent', function (Blueprint $table) {
            $table->dropColumn('crm_email_history_id');
        });
    }
}
