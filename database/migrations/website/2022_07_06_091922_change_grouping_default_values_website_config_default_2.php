<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;

class ChangeGroupingDefaultValuesWebsiteConfigDefault2 extends Migration
{
    /**
     * @throws \Doctrine\DBAL\Exception when the mapping could not be setup
     */
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
    public function up(): void
    {
        DB::statement(
            "ALTER TABLE `website_config_default`
                CHANGE `grouping` `grouping` enum(
                    'General',
                    'Home Page Display',
                    'Inventory Display',
                    'Contact Forms',
                    'Call to Action Pop-Up',
                    'Payment Calculator',
                    'Showroom Setup'
                ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;"
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        DB::statement(
            "ALTER TABLE `website_config_default`
                CHANGE `grouping` `grouping` enum(
                    'General',
                    'Home Page Display',
                    'Inventory Display',
                    'Contact Forms',
                    'Call to Action Pop-Up',
                    'Payment Calculator'
                ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;"
        );
    }
}
