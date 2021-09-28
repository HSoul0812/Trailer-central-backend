<?php

declare(strict_types=1);

namespace Tests\Integration\App\Support\Traits;

use App\Models\Inventory\InventoryLog;
use App\Models\Leads\LeadLog;
use Tests\Common\IntegrationTestCase;

class TableAwareTest extends IntegrationTestCase
{
    /**
     * @covers \App\Models\Inventory\InventoryLog::getTableName
     * @covers \App\Models\Leads\LeadLog::getTableName
     */
    public function testHasAnStaticInstancePerModel(): void
    {
        // it's just necessary to test two different models
        $expectedInventoryTableName = 'inventory_logs';
        $expectedLeadsTableName = 'lead_logs';

        $inventoryTableName = InventoryLog::getTableName();
        $leadsTableName = LeadLog::getTableName();

        $this->assertSame($expectedInventoryTableName, $inventoryTableName);
        $this->assertSame($expectedLeadsTableName, $leadsTableName);
    }
}
