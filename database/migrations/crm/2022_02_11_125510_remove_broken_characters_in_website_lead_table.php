<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class RemoveBrokenCharactersInWebsiteLeadTable extends Migration
{
    private const LEAD_ID = 5900721;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $lead = DB::table('website_lead')->where(['identifier' => self::LEAD_ID])->first(['comments', 'email_address']);
        if(!empty($lead->comments)) {
            $sanitizedComments = html_entity_decode(mb_convert_encoding(stripslashes($lead->comments), 'HTML-ENTITIES', 'UTF-8'));
            $sanitizedComments = preg_replace('/&#(\d+);/i', '', $sanitizedComments);

            $sanitizedEmail = html_entity_decode(mb_convert_encoding(stripslashes($lead->email_address), 'HTML-ENTITIES', 'UTF-8'));
            $sanitizedEmail = preg_replace('/&#(\d+);/i', '', $sanitizedEmail);

            DB::table('website_lead')
                ->where(['identifier' => self::LEAD_ID])
                ->update([
                    'comments' => $sanitizedComments,
                    'email_address' => $sanitizedEmail,
                ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
