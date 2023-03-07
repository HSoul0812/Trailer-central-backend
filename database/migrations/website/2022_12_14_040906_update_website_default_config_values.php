<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Website\Config\WebsiteConfigDefault;

class UpdateWebsiteDefaultConfigValues extends Migration
{
    private const ADD_DISABLED_OPTION = [
        'inventory/filters/hide_show_more',
        'inventory/filters/features'
    ];

    private const REPLACE_BR = [
        'form-success-action/general',
        'form-success-action/inventory'
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        WebsiteConfigDefault::whereIn('key', self::ADD_DISABLED_OPTION)->update([
            'values' => '{"0": "Disabled", "1": "Enabled"}'
        ]);

        WebsiteConfigDefault::whereIn('key', self::REPLACE_BR)->get()->each(function ($config) {
            $config->update([
                'label' => str_replace([PHP_EOL . '<br/>', '<br/>'], PHP_EOL, $config->label)
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
        WebsiteConfigDefault::whereIn('key', self::ADD_DISABLED_OPTION)->update([
            'values' => '{"1": "Enabled"}'
        ]);
    }
}
