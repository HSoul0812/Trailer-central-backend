<?php


use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddTimestampsToClappTransactionTable extends Migration
{
    /**
     * Dispatch Activity Equals
     */
    const DISPATCH_ACTIVITY_EMPTY = '0000-00-00 00:00:00';

    /**
     * Last Activity Start Date
     */
    const LAST_ACTIVITY_START = '2023-03-01';

    /**
     * Scheduled Start Date
     */
    const SCHEDULED_START = '2023-02-01';


    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clapp_session', function (Blueprint $table) {
            $table->index(['session_client'], 'SESSION_CLAPP_CLIENT_ID');
            $table->index(['session_last_activity'], 'SESSION_CLAPP_SESSION_LAST_ACTIVITY');
            $table->index(['dispatch_last_activity'], 'SESSION_CLAPP_DISPATCH_LAST_ACTIVITY');
        });

        DB::table('clapp_session')
            ->select('session_row_id')
            ->where('dispatch_last_activity', '=', self::DISPATCH_ACTIVITY_EMPTY)
            ->where('session_last_activity', '>', self::LAST_ACTIVITY_START)
            ->where('session_scheduled', '>', self::SCHEDULED_START)
            ->chunk(500, function (Collection $sessions) {
                $rows = [];
                foreach ($sessions as $session) {
                    $rows[] = $session->session_row_id;
                }

                DB::table('clapp_session')->whereIn('session_row_id', $rows)->update([
                    'dispatch_last_activity' => DB::raw('session_last_activity')
                ]);
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clapp_session', function (Blueprint $table) {
            $table->dropIndex('SESSION_CLAPP_CLIENT_ID');
            $table->dropIndex('SESSION_CLAPP_SESSION_LAST_ACTIVITY');
            $table->dropIndex('SESSION_CLAPP_DISPATCH_LAST_ACTIVITY');
        });
    }
}
