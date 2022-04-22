<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeGroupingDefaultValuesWebsiteConfigDefault extends Migration
{
    public function __construct()
    {
        // to avoid ENUM migration error
        // @see https://stackoverflow.com/questions/33140860/laravel-5-1-unknown-database-type-enum-requested
        DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement(
            "ALTER TABLE `website_config_default`
                CHANGE `type` `type` enum('enumerable', 'image', 'int', 'text', 'textarea', 'checkbox', 'enumerable_multiple') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;"
        );

        DB::statement(
            "ALTER TABLE `website_config_default`
                CHANGE `grouping` `grouping` enum('General', 'Home Page Display', 'Inventory Display', 'Contact Forms', 'Call to Action Pop-Up', 'Payment Calculator') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;"
        );

        Schema::table('website_config_default', function (Blueprint $table) {
            $table->text('values')->change();
            $table->text('default_value')->change();
        });

        // insert into trailercentral.website_config_default (key, private, type, label, note, grouping, values, values_mapping, default_label, default_value, sort_order) values ('payment-calculator/term-list', 0, 'enumerable_multiple', 'Terms list', '', 'Payment Calculator', '{"1":"12 Months (1 Year)","2":"24 Months (2 Years)","3":"36 Months (3 Years)","4":"48 Months (4 Years)","5":"60 Months (5 Years)","6":"72 Months (6 Years)","7":"84 Months (7 Years)","8":"96 Months (8 Years)","9":"108 Months (9 Years)","10":"120 Months (10 Years)","11":"132 Months (11 Years)","12":"144 Months (12 Years)","13":"156 Months (13 Years)","14":"168 Months (14 Years)","15":"180 Months (15 Years)","16":"192 Months (16 Years)","17":"204 Months (17 Years)","18":"216 Months (18 Years)","19":"228 Months (19 Years)","20":"240 Months (20 Years)"}', null, ' ', '["1","2","3","4","5","6","7","8","9","10","11","12","13","14","15","16","17","18","19","20"]', 1620);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement(
            "ALTER TABLE `website_config_default`
                CHANGE `type` `type` enum('enumerable', 'image', 'int', 'text', 'textarea', 'checkbox') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;"
        );

        DB::statement(
            "ALTER TABLE `website_config_default`
                CHANGE `grouping` `grouping` enum('General', 'Home Page Display', 'Inventory Display', 'Contact Forms', 'Call to Action Pop-Up') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;"
        );

        Schema::table('website_config_default', function (Blueprint $table) {
            $table->string('values', 525)->change();
            $table->string('default_value', 525)->change();
        });
    }
}
