<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddShowCaptchaToWebsiteConfigDefaultTable extends Migration
{
    private const SHOW_CAPTCHA_ON_CONTACT_AS_FORM_OPTION = [
        'key' => 'contact-as-form/show_captcha',
        'private' => 1,
        'type' => 'checkbox',
        'label' => 'Show Captcha On Contact Us Form',
        'grouping' => 'Contact Forms',
        'default_label' => '',
        'sort_order' => 620,
        'note' => 'Only Google recaptcha is supported'
    ];

    private const CAPTCHA_PUBLIC_KEY_OPTION = [
        'key' => 'contact-as-form/captcha_public_key',
        'private' => 1,
        'type' => 'text',
        'label' => 'Google reCAPTCHA V2 Public Key',
        'grouping' => 'Contact Forms',
        'default_label' => '',
        'sort_order' => 625,
        'note' => 'Only Google recaptcha is supported'
    ];

    private const CAPTCHA_SECRET_KEY_OPTION = [
        'key' => 'contact-as-form/captcha_secret_key',
        'private' => 1,
        'type' => 'text',
        'label' => 'Google reCAPTCHA V2 Secret Key',
        'grouping' => 'Contact Forms',
        'default_label' => '',
        'sort_order' => 630,
        'note' => 'Only Google recaptcha is supported'
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('website_config_default')->insert(self::SHOW_CAPTCHA_ON_CONTACT_AS_FORM_OPTION);
        DB::table('website_config_default')->insert(self::CAPTCHA_PUBLIC_KEY_OPTION);
        DB::table('website_config_default')->insert(self::CAPTCHA_SECRET_KEY_OPTION);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('website_config_default')->where('key', self::SHOW_CAPTCHA_ON_CONTACT_AS_FORM_OPTION['key'])->delete();
        DB::table('website_config_default')->where('key', self::CAPTCHA_PUBLIC_KEY_OPTION['key'])->delete();
        DB::table('website_config_default')->where('key', self::CAPTCHA_SECRET_KEY_OPTION['key'])->delete();
    }
}
