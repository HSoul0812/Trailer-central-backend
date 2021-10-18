<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class AddFbMessagesToInteractionMessageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE `interaction_message` CHANGE `tb_name` `tb_name` ENUM('crm_email_history','dealer_texts_log','fbapp_messages') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL");

        DB::table('fbapp_messages')
            ->select('id')
            ->orderBy('id')
            ->chunk(5000, function (Collection $emailHistory) {
                $interactionMessage = [];

                foreach ($emailHistory as $item) {
                    array_push($interactionMessage, [
                        'tb_primary_id' => $item->id,
                        'message_type' => 'fb',
                        'tb_name' => 'fbapp_messages',
                        'created_at' => Carbon::now(),
                    ]);
                }

                DB::table('interaction_message')->insert($interactionMessage);
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('interaction_message')->where(['tb_name' => 'fbapp_messages'])->delete();
        DB::statement("ALTER TABLE `interaction_message` CHANGE `tb_name` `tb_name` ENUM('crm_email_history','dealer_texts_log') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL");
    }
}
