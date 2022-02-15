<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixSchemaForCustomerInventories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        // We can't alter o replace triggers, so we need to drop them
        $this->dropTriggers();
        $this->dropProcedure();
        $this->dropTables();

        $this->createTables();
        // Procedure and triggers are only used as a last resort due that solution has three points where are created
        // references between inventory and customers.
        // When those solution points have been migrated to a better way e.g Eloquent event,
        // these mechanism must be dropped.
        $this->createProcedure();
        $this->createTriggers();
        $this->migrateData();
    }

    private function createProcedure(): void
    {
        $inventoryForACustomerHandler = <<<SQL
-- ==========================================================================
-- Insert a new record for the relation between customer and inventory
--
-- Parameters:
--   @customerId  - id of a valid customer
--   @inventoryId - id of a valid inventory
-- ==========================================================================
CREATE PROCEDURE InventoryForACustomerHandler(customerId INT,inventoryId INT)
BEGIN
    -- exception handlers
    -- every exception which is not 1062 will be stored in the table `exception_log`
    DECLARE EXIT HANDLER FOR 1062
    BEGIN
        -- do nothing
    END;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        GET DIAGNOSTICS CONDITION 1
                @sqlstate = RETURNED_SQLSTATE,
                @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
        SET @full_error = CONCAT('ERROR ', @errno, ' (', @sqlstate, '): ', @text);

        INSERT INTO exception_log (uuid, object_name, message) VALUES (UUID(), 'InventoryForACustomerHandler', @full_error);
    END;

    -- lets try to insert the new record
    INSERT INTO dms_customer_inventory (uuid, customer_id, inventory_id)
    VALUES (UUID(), customerId, inventoryId);
END;
SQL;

        DB::unprepared($inventoryForACustomerHandler);
    }

    private function createTriggers(): void
    {
        $inventoryForACustomerTriggers = <<<SQL
CREATE TRIGGER AfterInsertRepairOrder
AFTER INSERT
ON dms_repair_order FOR EACH ROW
BEGIN
    IF NEW.inventory_id IS NOT NULL AND
       NEW.inventory_id != 0 AND
       NEW.customer_id IS NOT NULL AND
       NEW.customer_id != 0 THEN
        CALL InventoryForACustomerHandler(NEW.customer_id, NEW.inventory_id);
    END IF;
END;

CREATE TRIGGER AfterInsertDmsUnitSale
AFTER INSERT
ON dms_unit_sale FOR EACH ROW
BEGIN
    IF NEW.inventory_id IS NOT NULL AND NEW.inventory_id != 0 THEN
        -- insert a new record to make a relation between buyer and inventory
        IF NEW.buyer_id IS NOT NULL AND NEW.buyer_id != 0 THEN
            CALL InventoryForACustomerHandler(NEW.buyer_id, NEW.inventory_id);
        END IF;
        -- insert a new record to make a relation between co-buyer and inventory
        IF NEW.cobuyer_id IS NOT NULL AND NEW.cobuyer_id != 0 THEN
            CALL InventoryForACustomerHandler(NEW.cobuyer_id, NEW.inventory_id);
        END IF;
    END IF;
END;

CREATE TRIGGER AfterInsertQbInvoiceItem
AFTER INSERT
ON qb_invoice_items FOR EACH ROW
BEGIN
    DECLARE customerId INT;
    DECLARE itemType VARCHAR(25);
    DECLARE inventoryId INT;

    -- exception handler
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        GET DIAGNOSTICS CONDITION 1
                @sqlstate = RETURNED_SQLSTATE,
                @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
        SET @full_error = CONCAT('ERROR ', @errno, ' (', @sqlstate, '): ', @text);

        INSERT INTO exception_log (uuid, object_name, message) VALUES (UUID(), 'AfterInsertQbInvoiceItem', @full_error);
    END;

    SET itemType = (
                SELECT I.type
                FROM qb_items I
                WHERE I.id = NEW.item_id LIMIT 1
            );

    -- when type is trailer, the inventory have to be attached to the customer
    IF itemType = 'trailer' THEN
        -- get customer id
        SET customerId = (
                SELECT I.customer_id
                FROM qb_invoices I
                WHERE I.id = NEW.invoice_id LIMIT 1
            );

        -- get inventory id
        SET inventoryId = (
                SELECT I.item_primary_id
                FROM qb_items I
                WHERE I.id = NEW.item_id LIMIT 1
            );

        IF inventoryId IS NOT NULL AND inventoryId != 0 THEN
            CALL InventoryForACustomerHandler(customerId, inventoryId);
        END IF;
    END IF;
END;
SQL;

        DB::unprepared($inventoryForACustomerTriggers);
    }

    private function createTables(): void
    {
        Schema::create('dms_customer_inventory', static function (Blueprint $table): void {
            $table->string('uuid', 38)->primary();
            $table->integer('customer_id')->unsigned();
            $table->integer('inventory_id')->unsigned();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('customer_id')->references('id')->on('dms_customer')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->foreign('inventory_id')->references('inventory_id')->on('inventory')->onDelete('CASCADE')->onUpdate('CASCADE');

            $table->unique(['customer_id', 'inventory_id']);
        });

        // For the next table we must to define a vacune politic to maintain a good performance
        Schema::create('exception_log', static function (Blueprint $table): void {
            $table->string('uuid', 38)->primary();
            $table->string('object_name')
                ->comment('procedure, function, or trigger name')
                ->index();
            $table->text('message');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    private function migrateData(): void
    {
        $sqlForMigrateAllData = <<<SQL
INSERT INTO dms_customer_inventory(uuid, customer_id, inventory_id)
SELECT * FROM (
     SELECT UUID(), RO.customer_id, RO.inventory_id
     FROM dms_repair_order RO
     JOIN inventory I ON RO.inventory_id = I.inventory_id
     JOIN dms_customer C ON RO.customer_id = C.id -- inventories from repair orders

     UNION

     SELECT UUID(), US.buyer_id AS customer_id, US.inventory_id FROM dms_unit_sale US
     JOIN inventory I ON US.inventory_id = I.inventory_id
     JOIN dms_customer C ON US.buyer_id = C.id -- inventories from unit sales by buyer

     UNION

     SELECT UUID(), US.cobuyer_id AS customer_id, US.inventory_id FROM dms_unit_sale US
     JOIN inventory I ON US.inventory_id = I.inventory_id
     JOIN dms_customer C ON US.cobuyer_id = C.id -- inventories from unit sales by co-buyer

     UNION

     SELECT UUID(), IV.customer_id, I.item_primary_id as inventory_id FROM qb_invoice_items II
     JOIN qb_items I ON II.item_id = I.id
     JOIN inventory IT ON IT.inventory_id = I.item_primary_id
     JOIN qb_invoices IV ON II.invoice_id = IV.id
     JOIN dms_customer C ON IV.customer_id = C.id
     WHERE I.type = 'trailer' -- inventories from invoice items

) cte_inventory GROUP BY customer_id, inventory_id;
SQL;

        DB::unprepared($sqlForMigrateAllData);
    }

    private function dropProcedure(): void
    {
        DB::unprepared(<<<SQL
 DROP PROCEDURE IF EXISTS InventoryForACustomerHandler;
SQL
        );
    }

    private function dropTriggers(): void
    {
        DB::unprepared(<<<SQL
 DROP TRIGGER IF EXISTS AfterInsertRepairOrder;
 DROP TRIGGER IF EXISTS AfterInsertQbInvoiceItem;
 DROP TRIGGER IF EXISTS AfterInsertDmsUnitSale;
SQL
        );
    }

    private function dropTables(): void
    {
        Schema::dropIfExists('dms_customer_inventory');
        Schema::dropIfExists('exception_log');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        $this->dropTriggers();
        $this->dropProcedure();
        $this->dropTables();
    }
}
