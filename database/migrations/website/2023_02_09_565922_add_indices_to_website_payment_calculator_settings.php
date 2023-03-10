<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddIndicesToWebsitePaymentCalculatorSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('website_payment_calculator_settings', static function (Blueprint $table) {
            $table->index(['website_id'], 'payment_settings_website_id_index');
            $table->index(['entity_type_id'], 'payment_settings_entity_type_id_index');
            $table->index(['inventory_condition'], 'payment_settings_inventory_condition_index');
            $table->index(['operator'], 'payment_settings_operator_index');
            $table->index(['financing'], 'payment_settings_financing_index');
            $table->index(['website_id', 'entity_type_id'], 'payment_settings_website_entity_index');
            $table->index(['website_id', 'entity_type_id', 'inventory_condition'], 'payment_settings_website_entity_condition_index');
            $table->index(['website_id', 'entity_type_id', 'inventory_condition', 'financing'], 'payment_settings_website_entity_condition_financing_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('website_payment_calculator_settings', static function (Blueprint $table) {
            $table->dropIndex('payment_settings_website_id_index');
            $table->dropIndex('payment_settings_entity_type_id_index');
            $table->dropIndex('payment_settings_inventory_condition_index');
            $table->dropIndex('payment_settings_operator_index');
            $table->dropIndex('payment_settings_financing_index');
            $table->dropIndex('payment_settings_website_entity_index');
            $table->dropIndex('payment_settings_website_entity_condition_index');
            $table->dropIndex('payment_settings_website_entity_condition_financing_index');
        });
    }
}
