<?php

namespace Tests\Integration\Models\Inventory\Inventory;

use App\Models\Inventory\Attribute;
use App\Models\Inventory\AttributeValue;
use App\Models\Inventory\Inventory;
use App\Models\User\DealerLocation;
use App\Models\User\Location\Geolocation;
use Illuminate\Foundation\Testing\WithFaker;
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
    use WithFaker;

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
            'code' => 'testattr_'.$this->faker->word(),
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

    public function testItUsesTheLocationFromGeolocationIfInventoryHasNoLocationAndDealerLocationHasNoCoords()
    {
        $dealerLocation = factory(DealerLocation::class)->create([
            'postalcode' => 'testzip'
        ]);

        //updating because the factory would use faker if the lat/lng is null
        $dealerLocation->update([
            'latitude' => null,
            'longitude' => null
        ]);

        $inventory = factory(Inventory::class)->create([
            'dealer_location_id' => $dealerLocation->dealer_location_id
        ]);

        $geolocation = Geolocation::create([
            'zip' => 'testzip',
            'latitude' => 1234,
            'longitude' => 1234,
            'country' => 'USA'
        ]);

        $inventoryGeolocation = $inventory->geolocationPoint();

        $this->assertSame(1234, $inventoryGeolocation->getLatitude());
        $this->assertSame(1234, $inventoryGeolocation->getLongitude());

        $inventory->delete();
        $geolocation->delete();
    }
}
