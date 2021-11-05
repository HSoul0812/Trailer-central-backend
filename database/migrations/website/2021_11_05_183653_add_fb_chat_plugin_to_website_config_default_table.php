<?php

use Illuminate\Database\Migrations\Migration;

class AddFbChatPluginToWebsiteConfigDefaultTable extends Migration
{
    private const FB_CHAT_PLUGIN_OPTION = [
        'key' => 'general/fbchat_plugin_code',
        'private' => 0,
        'type' => 'textarea',
        'label' => 'FB Chat Plugin',
        'note' => 'As a Faceboook Page Admin, Go to Page Settings > Messaging. ' .
                    'In the "Add Messenger to your website" section, click the ' .
                    '"Get Started" Button. Follow the instructions on-screen then ' .
                    'return here and paste the code snippet you receive.<br><br>' .
                    'You may also follow the instructions here: ' .
                    'https://developers.facebook.com/docs/messenger-platform/discovery/facebook-chat-plugin#setup_tool',
        'grouping' => 'General',
        'default_label' => '',
        'sort_order' => 1300,
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('website_config_default')->insert(self::FB_CHAT_PLUGIN_OPTION);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('website_config_default')->where('key', self::FB_CHAT_PLUGIN_OPTION['key'])->delete();
    }
}
