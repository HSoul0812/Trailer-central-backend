<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddMissingVariablesToWebsiteConfigDefaultTable extends Migration
{
    private const GENERAL_HEAD_SCRIPT = [
        'key' => 'general/head_script',
        'private' => 0,
        'type' => 'textarea',
        'label' => 'Head Scripts (admin visible only)',
        'note' => null,
        'grouping' => 'General',
        'sort_order' => 2325
    ];

    private const CALL_TO_ACTION_CUSTOM_TEXT = [
        'key' => 'call-to-action/custom-text',
        'private' => 0,
        'type' => 'textarea',
        'label' => 'Newsletter Sign-Up Form',
        'note' => 'Shows a form with name, e-mail, and what the person is looking for. Submissions go to your general inquiry e-mail address and website leads. (Default)',
        'grouping' => 'Call to Action Pop-Up',
        'sort_order' => 260
    ];

    private const CALL_TO_ACTION_RECAPTCHA = [
        'key' => 'call-to-action/recaptcha',
        'private' => 0,
        'type' => 'checkbox',
        'label' => 'Use reCAPTCHA',
        'note' => null,
        'grouping' => 'Call to Action Pop-Up',
        'default_value' => '0',
        'sort_order' => 255
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        DB::table('website_config_default')->insert(self::GENERAL_HEAD_SCRIPT);
        DB::table('website_config_default')->insert(self::CALL_TO_ACTION_CUSTOM_TEXT);
        DB::table('website_config_default')->insert(self::CALL_TO_ACTION_RECAPTCHA);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        DB::table('website_config_default')->where('key', self::GENERAL_HEAD_SCRIPT['key'])->delete();
        DB::table('website_config_default')->where('key', self::CALL_TO_ACTION_CUSTOM_TEXT['key'])->delete();
        DB::table('website_config_default')->where('key', self::CALL_TO_ACTION_RECAPTCHA['key'])->delete();
    }
}
