<?php

namespace Tests\Integration\Repositories\Website\PaymentCalculator;

use App\Models\Inventory\Inventory;
use App\Models\Website\PaymentCalculator\Settings;
use App\Repositories\Website\PaymentCalculator\SettingsRepositoryInterface;
use Tests\database\seeds\Inventory\InventorySeeder;
use Tests\database\seeds\Website\PaymentCalculator\SettingsSeeder;
use Tests\Integration\IntegrationTestCase;

/**
 * Class SettingRepositoryTest
 * @package Tests\Integration\Repositories\Website\PaymentCalculator
 *
 * @coversDefaultClass \App\Repositories\Website\PaymentCalculator\SettingsRepository
 */
class SettingRepositoryTest extends IntegrationTestCase
{
    /**
     * @var InventorySeeder
     */
    private $inventorySeeder;

    /**
     * @var SettingsSeeder[]
     */
    private $settingsSeeders;

    public function tearDown(): void
    {
        foreach ($this->settingsSeeders as $settingsSeeder) {
            $settingsSeeder->cleanUp();
        }

        $this->inventorySeeder->cleanUp();
    }

    /**
     * @covers ::getCalculatedSettingsByInventory
     *
     * @dataProvider settingsDataProvider
     *
     * @group DW
     * @group DW_INVENTORY
     */
    public function testGetCalculatedSettingsByInventory(array $inventorySeederParams, array $settingsSeederParams)
    {
        $this->inventorySeeder = $inventorySeeder = new InventorySeeder($inventorySeederParams);
        $inventorySeeder->seed();
        $inventory = $inventorySeeder->inventory;

        foreach ($settingsSeederParams as $settingsSeederParam) {
            $settingsSeederParam['settingsParams']['website_id'] = $inventory->user->website->id;
            $settingsSeeder = new SettingsSeeder($settingsSeederParam);
            $settingsSeeder->seed();
            $this->settingsSeeders[] = $settingsSeeder;
        }

        $settings = $this->settingsSeeders[0]->settings;

        /** @var SettingsRepositoryInterface $settingRepository */
        $settingRepository = app()->make(SettingsRepositoryInterface::class);

        $result = $settingRepository->getCalculatedSettingsByInventory($inventory);

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);

        $this->assertArrayHasKey('apr', $result);
        $this->assertNotNull($result['apr']);
        $this->assertEquals($settings->apr, $result['apr']);

        $expectedDown = $this->getExpectedDown($settings, $inventory);

        $this->assertArrayHasKey('down', $result);
        $this->assertNotNull($result['down']);
        $this->assertSame($expectedDown, $result['down']);

        $this->assertArrayHasKey('years', $result);
        $this->assertNotNull($result['years']);
        $this->assertSame($settings->months / 12, $result['years']);

        $this->assertArrayHasKey('months', $result);
        $this->assertNotNull($result['months']);
        $this->assertSame($settings->months, $result['months']);

        $expectedMonthlyPayment = $this->getExpectedMonthlyPayment($settings, $inventory);

        $this->assertArrayHasKey('monthly_payment', $result);
        $this->assertNotNull($result['monthly_payment']);
        $this->assertEquals($expectedMonthlyPayment, $result['monthly_payment']);

        $this->assertArrayHasKey('down_percentage', $result);
        $this->assertNotNull($result['down_percentage']);
        $this->assertEquals($settings->down, $result['down_percentage']);
    }

    /**
     * @covers ::getCalculatedSettingsByInventory
     *
     * @dataProvider noSettingsDataProvider
     *
     * @group DW
     * @group DW_INVENTORY
     */
    public function testGetCalculatedSettingsByInventoryNoSettingsAvailable(array $inventorySeederParams, array $settingsSeederParams)
    {
        $this->inventorySeeder = new InventorySeeder($inventorySeederParams);
        $this->inventorySeeder->seed();

        $inventorySeeder = $this->inventorySeeder;
        $inventory = $inventorySeeder->inventory;

        foreach ($settingsSeederParams as $settingsSeederParam) {
            $settingsSeederParam['settingsParams']['website_id'] = $inventory->user->website->id;
            $settingsSeeder = new SettingsSeeder($settingsSeederParam);
            $settingsSeeder->seed();
            $this->settingsSeeders[] = $settingsSeeder;
        }

        /** @var SettingsRepositoryInterface $settingRepository */
        $settingRepository = app()->make(SettingsRepositoryInterface::class);

        $result = $settingRepository->getCalculatedSettingsByInventory($inventory);

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);

        $this->assertArrayHasKey('apr', $result);
        $this->assertNull($result['apr']);

        $this->assertArrayHasKey('down', $result);
        $this->assertNull($result['down']);

        $this->assertArrayHasKey('years', $result);
        $this->assertNull($result['years']);

        $this->assertArrayHasKey('months', $result);
        $this->assertNull($result['months']);

        $this->assertArrayHasKey('monthly_payment', $result);
        $this->assertNull($result['monthly_payment']);

        $this->assertArrayHasKey('down_percentage', $result);
        $this->assertNull($result['down_percentage']);
    }

    /**
     * @param Settings $settings
     * @return float|int
     */
    private function getExpectedDown(Settings $settings, Inventory $inventory)
    {
        return $settings->down / 100 * $inventory->price;
    }

    /**
     * @param Settings $settings
     * @param Inventory $inventory
     * @return float|int
     */
    private function getExpectedMonthlyPayment(Settings $settings, Inventory $inventory)
    {
        $priceDown = (double)($settings->down / 100) * $inventory->price;
        $principal = $inventory->price - $priceDown;
        $interest = (double)$settings->apr / 100 / 12;
        $payments = $settings->months;
        $compInterest = (1 + $interest) ** $payments;

        return abs(number_format((float)($principal * $compInterest * $interest) / ($compInterest - 1), 2, '.', ''));
    }

    /**
     * @return array[]
     */
    public function settingsDataProvider(): array
    {
        $entityTypeId = 100000000 - rand(1, 10000);
        $inventoryCategoryId = 100000000 - rand(1, 10000);
        $inventoryPrice = 100000000 - rand(1, 10000);
        $inventoryCondition = 'new';

        $settingPriceOver = $inventoryPrice + 1;
        $settingsOperatorOver = 'over';

        $settingPriceLess = $inventoryPrice - 1;
        $settingsOperatorLessThan = 'less_than';

        return [
            'settingsOperatorOver' => [
                'inventorySeederParams' => [
                    'withInventory' => true,
                    'withWebsite' => true,
                    'entityTypeParams' => [
                        'entity_type_id' => $entityTypeId
                    ],
                    'inventoryParams' => [
                        'price' => $inventoryPrice,
                        'sales_price' => 0,
                        'msrp' => 0,
                        'condition' => $inventoryCondition
                    ]
                ],
                'settingsSeederParams' => [
                    [
                        'settingsParams' => [
                            'entity_type_id' => $entityTypeId,
                            'inventory_category_id' => null,
                            'inventory_price' => $settingPriceOver,
                            'inventory_condition' => $inventoryCondition,
                            'operator' => $settingsOperatorOver,
                            'financing' => 'financing',
                            'apr' => 10,
                            'down' => 30
                        ]
                    ],
                    [
                        'settingsParams' => [
                            'entity_type_id' => $entityTypeId,
                            'inventory_category_id' => null,
                            'inventory_price' => $settingPriceLess,
                            'inventory_condition' => $inventoryCondition,
                            'operator' => $settingsOperatorOver,
                            'financing' => 'no_financing',
                            'apr' => 10,
                            'down' => 30
                        ]
                    ],
                ],
            ],
            'settingsOperatorLess' => [
                'inventorySeederParams' => [
                    'withInventory' => true,
                    'withWebsite' => true,
                    'entityTypeParams' => [
                        'entity_type_id' => $entityTypeId
                    ],
                    'inventoryParams' => [
                        'price' => $inventoryPrice,
                        'sales_price' => 0,
                        'msrp' => 0,
                        'condition' => $inventoryCondition
                    ]
                ],
                'settingsSeederParams' => [
                    [
                        'settingsParams' => [
                            'entity_type_id' => $entityTypeId,
                            'inventory_category_id' => null,
                            'inventory_price' => $settingPriceLess,
                            'inventory_condition' => $inventoryCondition,
                            'operator' => $settingsOperatorLessThan,
                            'financing' => 'financing',
                            'apr' => 10,
                            'down' => 30
                        ]
                    ],
                    [
                        'settingsParams' => [
                            'entity_type_id' => $entityTypeId,
                            'inventory_category_id' => null,
                            'inventory_price' => $settingPriceOver,
                            'inventory_condition' => $inventoryCondition,
                            'operator' => $settingsOperatorLessThan,
                            'financing' => 'no_financing',
                            'apr' => 10,
                            'down' => 30
                        ]
                    ],
                ],
            ],
            'settingsWithCategory' => [
                'inventorySeederParams' => [
                    'withInventory' => true,
                    'withWebsite' => true,
                    'entityTypeParams' => [
                        'entity_type_id' => $entityTypeId
                    ],
                    'categoryParams' => [
                        'inventory_category_id' => $inventoryCategoryId,
                        'entity_type_id' => $entityTypeId
                    ],
                    'inventoryParams' => [
                        'price' => $inventoryPrice,
                        'sales_price' => 0,
                        'msrp' => 0,
                        'condition' => $inventoryCondition
                    ]
                ],
                'settingsSeederParams' => [
                    [
                        'settingsParams' => [
                            'entity_type_id' => $entityTypeId,
                            'inventory_category_id' => $inventoryCategoryId,
                            'inventory_price' => $settingPriceLess,
                            'inventory_condition' => $inventoryCondition,
                            'operator' => $settingsOperatorLessThan,
                            'financing' => 'financing',
                            'apr' => 10,
                            'down' => 30
                        ]
                    ]
                ],
            ],
        ];
    }

    /**
     * @return array[]
     */
    public function noSettingsDataProvider(): array
    {
        $entityTypeId = 100000000 - rand(1, 10000);
        $inventoryPrice = 100000000 - rand(1, 10000);
        $inventoryCondition = 'new';

        $settingPriceOver = $inventoryPrice + 1;
        $settingsOperatorOver = 'over';

        $settingPriceLess = $inventoryPrice - 1;
        $settingsOperatorLessThan = 'less_than';

        return [
            'settingsOperatorOver' => [
                'inventorySeederParams' => [
                    'withInventory' => true,
                    'withWebsite' => true,
                    'entityTypeParams' => [
                        'entity_type_id' => $entityTypeId
                    ],
                    'inventoryParams' => [
                        'price' => $inventoryPrice,
                        'sales_price' => 0,
                        'msrp' => 0,
                        'condition' => $inventoryCondition
                    ]
                ],
                'settingsSeederParams' => [
                    [
                        'settingsParams' => [
                            'entity_type_id' => $entityTypeId,
                            'inventory_category_id' => null,
                            'inventory_price' => $settingPriceLess,
                            'inventory_condition' => $inventoryCondition,
                            'operator' => $settingsOperatorOver,
                            'financing' => 'financing',
                            'apr' => 10,
                            'down' => 30
                        ]
                    ],
                    [
                        'settingsParams' => [
                            'entity_type_id' => $entityTypeId,
                            'inventory_category_id' => null,
                            'inventory_price' => $settingPriceLess,
                            'inventory_condition' => $inventoryCondition,
                            'operator' => $settingsOperatorOver,
                            'financing' => 'no_financing',
                            'apr' => 10,
                            'down' => 30
                        ]
                    ],
                ]
            ],
            'settingsOperatorLess' => [
                'inventorySeederParams' => [
                    'withInventory' => true,
                    'withWebsite' => true,
                    'entityTypeParams' => [
                        'entity_type_id' => $entityTypeId
                    ],
                    'inventoryParams' => [
                        'price' => $inventoryPrice,
                        'sales_price' => 0,
                        'msrp' => 0,
                        'condition' => $inventoryCondition
                    ]
                ],
                'settingsSeederParams' => [
                    [
                        'settingsParams' => [
                            'entity_type_id' => $entityTypeId,
                            'inventory_category_id' => null,
                            'inventory_price' => $settingPriceOver,
                            'inventory_condition' => $inventoryCondition,
                            'operator' => $settingsOperatorLessThan,
                            'financing' => 'financing',
                            'apr' => 10,
                            'down' => 30
                        ]
                    ],
                    [
                        'settingsParams' => [
                            'entity_type_id' => $entityTypeId,
                            'inventory_category_id' => null,
                            'inventory_price' => $settingPriceLess,
                            'inventory_condition' => $inventoryCondition,
                            'operator' => $settingsOperatorLessThan,
                            'financing' => 'no_financing',
                            'apr' => 10,
                            'down' => 30
                        ]
                    ],
                ]
            ],
            'settingsWithCategory' => [
                'inventorySeederParams' => [
                    'withInventory' => true,
                    'withWebsite' => true,
                    'entityTypeParams' => [
                        'entity_type_id' => $entityTypeId
                    ],
                    'categoryParams' => [
                        'entity_type_id' => $entityTypeId
                    ],
                    'inventoryParams' => [
                        'price' => $inventoryPrice,
                        'sales_price' => 0,
                        'msrp' => 0,
                        'condition' => $inventoryCondition
                    ]
                ],
                'settingsSeederParams' => [
                    [
                        'settingsParams' => [
                            'entity_type_id' => $entityTypeId,
                            'inventory_category_id' => 1,
                            'inventory_price' => $settingPriceLess,
                            'inventory_condition' => $inventoryCondition,
                            'operator' => $settingsOperatorLessThan,
                            'financing' => 'financing',
                            'apr' => 10,
                            'down' => 30
                        ]
                    ]
                ]
            ],
            'settingsAnotherEntityType' => [
                'inventorySeederParams' => [
                    'withInventory' => true,
                    'withWebsite' => true,
                    'entityTypeParams' => [
                        'entity_type_id' => $entityTypeId
                    ],
                    'inventoryParams' => [
                        'price' => $inventoryPrice,
                        'sales_price' => 0,
                        'msrp' => 0,
                        'condition' => $inventoryCondition
                    ]
                ],
                'settingsSeederParams' => [
                    [
                        'settingsParams' => [
                            'entity_type_id' => 1,
                            'inventory_price' => $settingPriceLess,
                            'inventory_condition' => $inventoryCondition,
                            'operator' => $settingsOperatorLessThan,
                            'financing' => 'financing',
                            'apr' => 10,
                            'down' => 30
                        ]
                    ]
                ]
            ],
        ];
    }
}
