<?php

namespace Tests\Integration\Models\Inventory\Inventory;

use App\Models\Inventory\Attribute;
use App\Models\Inventory\AttributeValue;
use App\Models\Inventory\Inventory;
use Tests\TestCase;

/**
 * @group DW
 * @group DW_INVENTORY
 *
 * @package Tests\Integration\Models\Inventory\Inventory
 *
 * @coversDefaultClass \App\Models\Inventory\Inventory
 */
class InventoryTest extends TestCase
{
    /**
     * @covers ::getAttributeById
     *
     * @group DW
     * @group DW_INVENTORY
     * @group DW_ELASTICSEARCH
     */
    public function testItValidatesSelectAttributeValues()
    {
        $inventory = factory(Inventory::class)->create();
        $attribute = Attribute::create([
            'type' => 'select',
            'code' => 'testattr',
            'name' => 'Test Attribute',
            'values' => 'slant:Slant,straight:Straight,head_head:Head to Head,reverse_slant:Reverse Slant',
        ]);

        $attributeValue = AttributeValue::create([
            'attribute_id' => $attribute->attribute_id,
            'inventory_id' => $inventory->inventory_id,
            'value' => 'someinvalidvalue'
        ]);

        $this->assertNull($inventory->getAttributeById($attribute->attribute_id));

        //update to a valid value
        $attributeValue->update(['value' => 'Straight']);

        //reset cache for eav_attribute_values
        $inventory = Inventory::whereInventoryId($inventory->inventory_id)->first();

        $this->assertSame('straight', $inventory->getAttributeById($attribute->attribute_id));

        $inventory->delete();
        $attribute->delete();
        $attributeValue->delete();
    }
}
