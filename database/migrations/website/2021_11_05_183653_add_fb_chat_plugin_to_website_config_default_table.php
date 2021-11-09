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
                    '"Get Started" Button. Configure your Chat Plugin the way you ' .
                    'want it to look, then click "Set Up" next to "Set Up Your Chat ' .
                    'Plugin" to receive a code to paste in here.',
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
