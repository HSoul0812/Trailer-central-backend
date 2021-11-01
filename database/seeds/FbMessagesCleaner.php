<?php

use App\Models\CRM\Interactions\Facebook\Conversation;
use App\Models\CRM\Interactions\Facebook\Message;
use App\Models\Integration\Facebook\Page;
use Illuminate\Database\Seeder;

class FbMessagesCleaner extends Seeder
{
    /**
     * @const Production Environment
     */
    const ENV_PROD = 'production';

    /**
     * @const Production Environment URL
     */
    const ENV_PROD_URL = 'http://api.v1.staging.trailercentral.com';

    /**
     * @const Dealer ID to Delete Messages From
     */
    const DEALER_ID = 1001;


    /**
     * Run the database seeds.
     * 
     * One-time use cleaner to delete all existing facebook messages
     * Deleting messages also removes them from interaction_messages,
     * then they'll be able to be re-imported correctly
     * 
     * DO NOT run this on production!
     *
     * @return void
     */
    public function run()
    {
        // Do NOT Run This on Production!
        if(env('APP_ENV') === self::ENV_PROD || env('APP_URL') === self::ENV_PROD_URL) {
            die('Do NOT run this on production!' . PHP_EOL . PHP_EOL);
        }


        // Get All Facebook Messages
        $messages = Message::leftJoin(Conversation::getTableName(), Conversation::getTableName().'.conversation_id', '=', Message::getTableName().'.conversation_id')
                           ->leftJoin(Page::getTableName(), Page::getTableName().'.page_id',  '=', Conversation::getTableName().'.page_id')
                           ->where(Page::getTableName().'.dealer_id', self::DEALER_ID)->get();

        // Delete All
        foreach($messages as $message) {
            Message::where('message_id', $message->message_id)->delete();
        }
        die('Deleted ' . $messages->count() . ' messages');
    }
}
