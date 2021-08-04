<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;

class AddLocationToEnumOnQuickbooksApprovalsDeleted extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $ddl = <<<SQL
ALTER TABLE `quickbook_approval_deleted` MODIFY `tb_name` ENUM(
    'qb_bills',
    'qb_bill_payment',
    'qb_items',
    'qb_vendors',
    'dealer_employee',
    'qb_invoices',
    'dms_customer',
    'qb_payment',
    'crm_pos_sales',
    'dms_unit_sale',
    'dms_unit_sale_payment',
    'qb_accounts',
    'qb_item_category',
    'qb_payment_methods',
    'qb_journal_entry',
    'qb_expenses',
    'qb_items_new',
    'inventory_floor_plan_payment',
    'dealer_refunds',
    'dealer_location') not null;
SQL;

        DB::statement($ddl);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        $ddl = <<<SQL
ALTER TABLE `quickbook_approval_deleted` MODIFY `tb_name` ENUM(
    'qb_bills',
    'qb_bill_payment',
    'qb_items',
    'qb_vendors',
    'dealer_employee',
    'qb_invoices',
    'dms_customer',
    'qb_payment',
    'crm_pos_sales',
    'dms_unit_sale',
    'dms_unit_sale_payment',
    'qb_accounts',
    'qb_item_category',
    'qb_payment_methods',
    'qb_journal_entry',
    'qb_expenses',
    'qb_items_new',
    'inventory_floor_plan_payment',
    'dealer_refunds') not null;
SQL;

        DB::statement($ddl);
    }
}
