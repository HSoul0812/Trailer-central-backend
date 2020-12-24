<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSchemaForCustomerInventories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $this->createTable();
        // Procedure and triggers are only used as a last resort due that solution has three points where are created
        // references between inventory and customers.
        // When those solution points have been migrated to a better way e.g Eloquent event,
        // these mechanism must be dropped.
        $this->createProcedure();
        $this->createTriggers();
    }

    private function createProcedure(): void
    {
        $inventoryForACustomerHandler = <<<SQL
-- =========================================================
-- Insert a new record for the relation between customer and
-- inventory, otherwise raise a error message.
--
-- Parameters:
--   @customerId  - id of a valid customer
--   @inventoryId - id of a valid inventory
-- Returns: nothing
-- =========================================================
CREATE PROCEDURE InventoryForACustomerHandler(
    customerId INT,
    inventoryId INT
)
BEGIN
    DECLARE dealerIdFromCustomerTable INT;
    DECLARE dealerIdFromInventoryTable INT;

    -- gets the dealer_id from `dms_customer` and `inventory`
    -- to verify the dealer ownership
    SELECT dealer_id
    INTO dealerIdFromCustomerTable
    FROM dms_customer WHERE id = customerId;

    SELECT dealer_id
    INTO dealerIdFromInventoryTable
    FROM inventory WHERE inventory_id = inventoryId;

    IF (
        dealerIdFromCustomerTable IS NOT NULL AND
        dealerIdFromInventoryTable IS NOT NULL
       ) AND dealerIdFromCustomerTable = dealerIdFromInventoryTable
    THEN
        -- only insert into dms_customer_inventory when that record
        -- does not exist
        INSERT IGNORE INTO dms_customer_inventory (customer_id, inventory_id)
        VALUES (customerId, inventoryId);
    ELSE
        -- raise a warning message
        SIGNAL SQLSTATE '01000'
            SET MESSAGE_TEXT = 'The inventory unit is not owned by the dealer',
                MYSQL_ERRNO = 1000;
    END IF;
END
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
    IF NEW.inventory_id IS NOT NULL AND NEW.inventory_id != 0 THEN
        CALL InventoryForACustomerHandler(NEW.customer_id, NEW.inventory_id);
    END IF;
END;

CREATE TRIGGER AfterInsertDmsUnitSale
AFTER INSERT
ON dms_unit_sale FOR EACH ROW
BEGIN
    IF NEW.inventory_id IS NOT NULL AND NEW.inventory_id != 0 THEN
        IF NEW.buyer_id IS NOT NULL THEN
            CALL InventoryForACustomerHandler(NEW.buyer_id, NEW.inventory_id);
        END IF;

        IF NEW.cobuyer_id IS NOT NULL THEN
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

    private function createTable(): void
    {
        Schema::create('dms_customer_inventory', static function (Blueprint $table): void {
            $table->increments('id')->unsigned();
            $table->integer('customer_id')->unsigned();
            $table->integer('inventory_id')->unsigned();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('customer_id')->references('id')->on('dms_customer');
            $table->foreign('inventory_id')->references('inventory_id')->on('inventory');
            $table->unique(['customer_id', 'inventory_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        DB::unprepared(<<<SQL
 DROP PROCEDURE IF EXISTS InventoryForACustomerHandler
SQL
        );

        DB::unprepared(<<<SQL
 DROP TRIGGER IF EXISTS InventoryForACustomerMaterializer
SQL
        );

        Schema::drop('dms_customer_inventory');
    }
}
