<?php

namespace Tests\Integration\Repositories\Dms\ServiceOrder;

use App\Exceptions\Tests\MissingTestDealerIdException;
use App\Models\CRM\Account\Invoice;
use App\Models\CRM\Account\InvoiceItem;
use App\Models\CRM\Dms\Quickbooks\Item;
use App\Models\CRM\Dms\ServiceOrder;
use App\Models\CRM\Dms\ServiceOrder\ServiceItem;
use App\Models\CRM\Dms\ServiceOrder\ServiceItemTechnician;
use App\Models\CRM\Dms\ServiceOrder\Technician;
use App\Models\CRM\Dms\UnitSale;
use App\Models\CRM\User\Customer;
use App\Models\Inventory\Inventory;
use App\Repositories\Dms\ServiceOrder\ServiceItemTechnicianRepository;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Class ServiceItemTechnicianRepositoryTest
 * @package Tests\Unit\Repositories\Dms\ServiceOrder
 *
 * @coversDefaultClass \App\Repositories\Dms\ServiceOrder\ServiceItemTechnicianRepository
 */
class ServiceItemTechnicianRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @covers ::serviceReport
     * @dataProvider serviceReportProvider
     *
     * @group DMS
     * @group DMS_SERVICE_ORDER
     *
     * @param array $serviceTechnician11
     * @param array $serviceTechnician12
     * @param array $serviceTechnician21
     * @throws MissingTestDealerIdException
     * @throws BindingResolutionException
     */
    public function testServiceReport(array $serviceTechnician11, array $serviceTechnician12, array $serviceTechnician21)
    {
        $customerId11 = factory(Customer::class)->create([
            'display_name' => $serviceTechnician11['customer_name'],
        ])->id;

        $customerId12 = factory(Customer::class)->create([
            'display_name' => $serviceTechnician12['customer_name'],
        ])->id;

        $customerId21 = factory(Customer::class)->create([
            'display_name' => $serviceTechnician21['customer_name'],
        ])->id;

        $inventory11 = factory(Inventory::class)->create([
            'dealer_id' => self::getTestDealerId(),
            'notes' => 'inventory11'
        ]);

        $inventory12 = factory(Inventory::class)->create([
            'dealer_id' => self::getTestDealerId(),
            'notes' => 'inventory12'
        ]);

        $unitSaleId11 = factory(UnitSale::class)->create([
            'sales_person_id' => $serviceTechnician11['sales_person_id'],
            'buyer_id' => $customerId11,
            'inventory_id' => $inventory11->inventory_id
        ])->id;

        $unitSaleId12 = factory(UnitSale::class)->create([
            'sales_person_id' => $serviceTechnician12['sales_person_id'],
            'buyer_id' => $customerId12,
            'inventory_id' => $inventory12->inventory_id
        ])->id;

        $unitSaleId21 = factory(UnitSale::class)->create([
            'sales_person_id' => $serviceTechnician21['sales_person_id'],
            'buyer_id' => $customerId21,
            'inventory_id' => false
        ])->id;

        $invoiceId11 = factory(Invoice::class)->create([
            'unit_sale_id' => $unitSaleId11,
            'total' => $serviceTechnician11['invoice_total'],
            'doc_num' => $serviceTechnician11['doc_num'],
            'invoice_date' => $serviceTechnician11['sale_date'],
        ])->id;

        $invoiceId12 = factory(Invoice::class)->create([
            'unit_sale_id' => $unitSaleId12,
            'total' => $serviceTechnician12['invoice_total'],
            'doc_num' => $serviceTechnician12['doc_num'],
            'invoice_date' => $serviceTechnician12['sale_date'],
        ])->id;

        $invoiceId21 = factory(Invoice::class)->create([
            'unit_sale_id' => $unitSaleId21,
            'total' => $serviceTechnician21['invoice_total'],
            'doc_num' => $serviceTechnician21['doc_num'],
            'invoice_date' => $serviceTechnician21['sale_date'],
        ])->id;

        $invoices = [$invoiceId11 => $serviceTechnician11, $invoiceId12 => $serviceTechnician12, $invoiceId21 => $serviceTechnician21];

        foreach ($invoices as $invoiceId => $unitSaleLabor) {
            foreach ($unitSaleLabor['qb_invoice_items'] as $qbInvoiceItem) {
                $itemId = factory(Item::class)->create([
                    'cost' => $qbInvoiceItem['cost'],
                    'type' => $qbInvoiceItem['type'],
                ])->id;

                factory(InvoiceItem::class)->create([
                    'item_id' => $itemId,
                    'invoice_id' => $invoiceId,
                    'unit_price' => $qbInvoiceItem['unit_price'],
                    'qty' => $qbInvoiceItem['qty'],
                ]);
            }
        }

        $technician1 = factory(Technician::class)->create([]);
        $technician2 = factory(Technician::class)->create([]);

        $technicianId1 = $technician1->id;
        $technicianId2 = $technician2->id;

        $this->createServiceItemTechnician($serviceTechnician11, $unitSaleId11, $technicianId1);
        $this->createServiceItemTechnician($serviceTechnician12, $unitSaleId12, $technicianId1);
        $this->createServiceItemTechnician($serviceTechnician21, $unitSaleId21, $technicianId2);

        /** @var ServiceItemTechnicianRepository $repository */
        $repository = app()->make(ServiceItemTechnicianRepository::class);

        $result = $repository->serviceReport(['dealer_id' => $this->getTestDealerId()]);

        $this->assertArrayHasKey($technicianId1, $result);
        $this->assertArrayHasKey($technicianId2, $result);

        $unitSale11Key = array_search($unitSaleId11, array_column($result[$technicianId1], 'sale_id'));
        $unitSale12Key = array_search($unitSaleId12, array_column($result[$technicianId1], 'sale_id'));
        $unitSale21Key = array_search($unitSaleId21, array_column($result[$technicianId2], 'sale_id'));

        $notExistingKey = array_search($unitSaleId11, array_column($result[$technicianId2], 'sale_id'));

        $this->assertNotFalse($unitSale11Key);
        $this->assertNotFalse($unitSale12Key);
        $this->assertNotFalse($unitSale21Key);

        $this->assertFalse($notExistingKey);

        $this->assertArrayHasKey('dealer_id', $result[$technicianId1][$unitSale11Key]);
        $this->assertArrayHasKey('dealer_id', $result[$technicianId1][$unitSale12Key]);
        $this->assertArrayHasKey('dealer_id', $result[$technicianId2][$unitSale21Key]);

        foreach ($result as $technician) {
            $dealerIds = array_unique(array_column($technician, 'dealer_id'));
            $this->assertCount(1, $dealerIds);
            $this->assertEquals($this->getTestDealerId(), $dealerIds[0]);
        }

        $this->assertArrayHasKey('act_hrs', $result[$technicianId1][$unitSale11Key]);
        $this->assertArrayHasKey('act_hrs', $result[$technicianId1][$unitSale12Key]);
        $this->assertArrayHasKey('act_hrs', $result[$technicianId2][$unitSale21Key]);

        $this->assertEquals($serviceTechnician11['act_hrs'], $result[$technicianId1][$unitSale11Key]['act_hrs']);
        $this->assertEquals($serviceTechnician12['act_hrs'], $result[$technicianId1][$unitSale12Key]['act_hrs']);
        $this->assertEquals($serviceTechnician21['act_hrs'], $result[$technicianId2][$unitSale21Key]['act_hrs']);

        $this->assertArrayHasKey('paid_hrs', $result[$technicianId1][$unitSale11Key]);
        $this->assertArrayHasKey('paid_hrs', $result[$technicianId1][$unitSale12Key]);
        $this->assertArrayHasKey('paid_hrs', $result[$technicianId2][$unitSale21Key]);

        $this->assertEquals($serviceTechnician11['paid_hrs'], $result[$technicianId1][$unitSale11Key]['paid_hrs']);
        $this->assertEquals($serviceTechnician12['paid_hrs'], $result[$technicianId1][$unitSale12Key]['paid_hrs']);
        $this->assertEquals($serviceTechnician21['paid_hrs'], $result[$technicianId2][$unitSale21Key]['paid_hrs']);

        $this->assertArrayHasKey('billed_hrs', $result[$technicianId1][$unitSale11Key]);
        $this->assertArrayHasKey('billed_hrs', $result[$technicianId1][$unitSale12Key]);
        $this->assertArrayHasKey('billed_hrs', $result[$technicianId2][$unitSale21Key]);

        $this->assertEquals($serviceTechnician11['billed_hrs'], $result[$technicianId1][$unitSale11Key]['billed_hrs']);
        $this->assertEquals($serviceTechnician12['billed_hrs'], $result[$technicianId1][$unitSale12Key]['billed_hrs']);
        $this->assertEquals($serviceTechnician21['billed_hrs'], $result[$technicianId2][$unitSale21Key]['billed_hrs']);

        $this->assertArrayHasKey('repair_order_type', $result[$technicianId1][$unitSale11Key]);
        $this->assertArrayHasKey('repair_order_type', $result[$technicianId1][$unitSale12Key]);
        $this->assertArrayHasKey('repair_order_type', $result[$technicianId2][$unitSale21Key]);

        $this->assertEquals($serviceTechnician11['repair_order_type'], $result[$technicianId1][$unitSale11Key]['repair_order_type']);
        $this->assertEquals($serviceTechnician12['repair_order_type'], $result[$technicianId1][$unitSale12Key]['repair_order_type']);
        $this->assertEquals($serviceTechnician21['repair_order_type'], $result[$technicianId2][$unitSale21Key]['repair_order_type']);

        $this->assertArrayHasKey('paid_retail', $result[$technicianId1][$unitSale11Key]);
        $this->assertArrayHasKey('paid_retail', $result[$technicianId1][$unitSale12Key]);
        $this->assertArrayHasKey('paid_retail', $result[$technicianId2][$unitSale21Key]);

        $this->assertEquals($serviceTechnician11['paid_retail'], $result[$technicianId1][$unitSale11Key]['paid_retail']);
        $this->assertEquals($serviceTechnician12['paid_retail'], $result[$technicianId1][$unitSale12Key]['paid_retail']);
        $this->assertEquals($serviceTechnician21['paid_retail'], $result[$technicianId2][$unitSale21Key]['paid_retail']);

        $this->assertArrayHasKey('first_name', $result[$technicianId1][$unitSale11Key]);
        $this->assertArrayHasKey('first_name', $result[$technicianId1][$unitSale12Key]);
        $this->assertArrayHasKey('first_name', $result[$technicianId2][$unitSale21Key]);

        $this->assertEquals($technician1->first_name, $result[$technicianId1][$unitSale11Key]['first_name']);
        $this->assertEquals($technician1->first_name, $result[$technicianId1][$unitSale12Key]['first_name']);
        $this->assertEquals($technician2->first_name, $result[$technicianId2][$unitSale21Key]['first_name']);

        $this->assertArrayHasKey('last_name', $result[$technicianId1][$unitSale11Key]);
        $this->assertArrayHasKey('last_name', $result[$technicianId1][$unitSale12Key]);
        $this->assertArrayHasKey('last_name', $result[$technicianId2][$unitSale21Key]);

        $this->assertEquals($technician1->last_name, $result[$technicianId1][$unitSale11Key]['last_name']);
        $this->assertEquals($technician1->last_name, $result[$technicianId1][$unitSale12Key]['last_name']);
        $this->assertEquals($technician2->last_name, $result[$technicianId2][$unitSale21Key]['last_name']);

        $this->assertArrayHasKey('invoice_id', $result[$technicianId1][$unitSale11Key]);
        $this->assertArrayHasKey('invoice_id', $result[$technicianId1][$unitSale12Key]);
        $this->assertArrayHasKey('invoice_id', $result[$technicianId2][$unitSale21Key]);

        $this->assertEquals($invoiceId11, $result[$technicianId1][$unitSale11Key]['invoice_id']);
        $this->assertEquals($invoiceId12, $result[$technicianId1][$unitSale12Key]['invoice_id']);
        $this->assertEquals($invoiceId21, $result[$technicianId2][$unitSale21Key]['invoice_id']);

        $this->assertArrayHasKey('invoice_total', $result[$technicianId1][$unitSale11Key]);
        $this->assertArrayHasKey('invoice_total', $result[$technicianId1][$unitSale12Key]);
        $this->assertArrayHasKey('invoice_total', $result[$technicianId2][$unitSale21Key]);

        $this->assertEquals($serviceTechnician11['invoice_total'], $result[$technicianId1][$unitSale11Key]['invoice_total']);
        $this->assertEquals($serviceTechnician12['invoice_total'], $result[$technicianId1][$unitSale12Key]['invoice_total']);
        $this->assertEquals($serviceTechnician21['invoice_total'], $result[$technicianId2][$unitSale21Key]['invoice_total']);

        $this->assertArrayHasKey('doc_num', $result[$technicianId1][$unitSale11Key]);
        $this->assertArrayHasKey('doc_num', $result[$technicianId1][$unitSale12Key]);
        $this->assertArrayHasKey('doc_num', $result[$technicianId2][$unitSale21Key]);

        $this->assertEquals($serviceTechnician11['doc_num'], $result[$technicianId1][$unitSale11Key]['doc_num']);
        $this->assertEquals($serviceTechnician12['doc_num'], $result[$technicianId1][$unitSale12Key]['doc_num']);
        $this->assertEquals($serviceTechnician21['doc_num'], $result[$technicianId2][$unitSale21Key]['doc_num']);

        $this->assertArrayHasKey('sale_date', $result[$technicianId1][$unitSale11Key]);
        $this->assertArrayHasKey('sale_date', $result[$technicianId1][$unitSale12Key]);
        $this->assertArrayHasKey('sale_date', $result[$technicianId2][$unitSale21Key]);

        $this->assertEquals($serviceTechnician11['sale_date']->format('Y-m-d'), $result[$technicianId1][$unitSale11Key]['sale_date']);
        $this->assertEquals($serviceTechnician12['sale_date']->format('Y-m-d'), $result[$technicianId1][$unitSale12Key]['sale_date']);
        $this->assertEquals($serviceTechnician21['sale_date']->format('Y-m-d'), $result[$technicianId2][$unitSale21Key]['sale_date']);

        $this->assertArrayHasKey('sales_person_id', $result[$technicianId1][$unitSale11Key]);
        $this->assertArrayHasKey('sales_person_id', $result[$technicianId1][$unitSale12Key]);
        $this->assertArrayHasKey('sales_person_id', $result[$technicianId2][$unitSale21Key]);

        $this->assertEquals($serviceTechnician11['sales_person_id'], $result[$technicianId1][$unitSale11Key]['sales_person_id']);
        $this->assertEquals($serviceTechnician12['sales_person_id'], $result[$technicianId1][$unitSale12Key]['sales_person_id']);
        $this->assertEquals($serviceTechnician21['sales_person_id'], $result[$technicianId2][$unitSale21Key]['sales_person_id']);

        $this->assertArrayHasKey('customer_name', $result[$technicianId1][$unitSale11Key]);
        $this->assertArrayHasKey('customer_name', $result[$technicianId1][$unitSale12Key]);
        $this->assertArrayHasKey('customer_name', $result[$technicianId2][$unitSale21Key]);

        $this->assertEquals($serviceTechnician11['customer_name'], $result[$technicianId1][$unitSale11Key]['customer_name']);
        $this->assertEquals($serviceTechnician12['customer_name'], $result[$technicianId1][$unitSale12Key]['customer_name']);
        $this->assertEquals($serviceTechnician21['customer_name'], $result[$technicianId2][$unitSale21Key]['customer_name']);

        $this->assertArrayHasKey('unit_sale_amount', $result[$technicianId1][$unitSale11Key]);
        $this->assertArrayHasKey('unit_sale_amount', $result[$technicianId1][$unitSale12Key]);
        $this->assertArrayHasKey('unit_sale_amount', $result[$technicianId2][$unitSale21Key]);

        $this->assertEquals($serviceTechnician11['invoice_total'], $result[$technicianId1][$unitSale11Key]['unit_sale_amount']);
        $this->assertEquals($serviceTechnician12['invoice_total'], $result[$technicianId1][$unitSale12Key]['unit_sale_amount']);
        $this->assertEmpty($result[$technicianId2][$unitSale21Key]['unit_sale_amount']);

        $expectedUnitCostAmount11 = array_sum(array_column($serviceTechnician11['qb_invoice_items'], 'cost'));

        $expectedUnitCostAmount12 = array_sum(array_column(array_filter($serviceTechnician12['qb_invoice_items'], function ($item) {
            return $item['type'] === 'trailer';
        }), 'cost'));

        $this->assertArrayHasKey('unit_cost_amount', $result[$technicianId1][$unitSale11Key]);
        $this->assertArrayHasKey('unit_cost_amount', $result[$technicianId1][$unitSale12Key]);
        $this->assertArrayHasKey('unit_cost_amount', $result[$technicianId2][$unitSale21Key]);

        $this->assertEquals($expectedUnitCostAmount11, $result[$technicianId1][$unitSale11Key]['unit_cost_amount']);
        $this->assertEquals($expectedUnitCostAmount12, $result[$technicianId1][$unitSale12Key]['unit_cost_amount']);
        $this->assertEmpty($result[$technicianId2][$unitSale21Key]['unit_cost_amount']);

        $expectedPartSaleAmount21 = array_reduce($serviceTechnician21['qb_invoice_items'], function ($total, $invoiceItem) {
            $total += $invoiceItem['unit_price'] * $invoiceItem['qty'];
            return $total;
        });

        $this->assertArrayHasKey('part_sale_amount', $result[$technicianId1][$unitSale11Key]);
        $this->assertArrayHasKey('part_sale_amount', $result[$technicianId1][$unitSale12Key]);
        $this->assertArrayHasKey('part_sale_amount', $result[$technicianId2][$unitSale21Key]);

        $this->assertEmpty($result[$technicianId1][$unitSale11Key]['part_sale_amount']);
        $this->assertEmpty($result[$technicianId1][$unitSale12Key]['part_sale_amount']);
        $this->assertEquals($expectedPartSaleAmount21, $result[$technicianId2][$unitSale21Key]['part_sale_amount']);

        $expectedPartCostAmount21 = array_sum(array_column($serviceTechnician21['qb_invoice_items'], 'cost'));

        $this->assertArrayHasKey('part_cost_amount', $result[$technicianId1][$unitSale11Key]);
        $this->assertArrayHasKey('part_cost_amount', $result[$technicianId1][$unitSale12Key]);
        $this->assertArrayHasKey('part_cost_amount', $result[$technicianId2][$unitSale21Key]);

        $this->assertEmpty($result[$technicianId1][$unitSale11Key]['part_cost_amount']);
        $this->assertEmpty($result[$technicianId1][$unitSale12Key]['part_cost_amount']);
        $this->assertEquals($expectedPartCostAmount21, $result[$technicianId2][$unitSale21Key]['part_cost_amount']);

        $expectedLaborSaleAmount12 = array_reduce($serviceTechnician12['qb_invoice_items'], function ($total, $invoiceItem) {
            $total += $invoiceItem['unit_price'] * $invoiceItem['qty'];
            return $total;
        });

        $this->assertArrayHasKey('labor_sale_amount', $result[$technicianId1][$unitSale11Key]);
        $this->assertArrayHasKey('labor_sale_amount', $result[$technicianId1][$unitSale12Key]);
        $this->assertArrayHasKey('labor_sale_amount', $result[$technicianId2][$unitSale21Key]);

        $this->assertEmpty($result[$technicianId1][$unitSale11Key]['labor_sale_amount']);
        $this->assertEquals($expectedLaborSaleAmount12, $result[$technicianId1][$unitSale12Key]['labor_sale_amount']);
        $this->assertEmpty($result[$technicianId2][$unitSale21Key]['labor_sale_amount']);

        $expectedLaborCostAmount12 = array_sum(array_column(array_filter($serviceTechnician12['qb_invoice_items'], function ($item) {
            return $item['type'] === 'labor';
        }), 'cost'));

        $this->assertArrayHasKey('labor_cost_amount', $result[$technicianId1][$unitSale11Key]);
        $this->assertArrayHasKey('labor_cost_amount', $result[$technicianId1][$unitSale12Key]);
        $this->assertArrayHasKey('labor_cost_amount', $result[$technicianId2][$unitSale21Key]);

        $this->assertEmpty($result[$technicianId1][$unitSale11Key]['labor_cost_amount']);
        $this->assertEquals($expectedLaborCostAmount12, $result[$technicianId1][$unitSale12Key]['labor_cost_amount']);
        $this->assertEmpty($result[$technicianId2][$unitSale21Key]['labor_cost_amount']);

        $this->assertArrayHasKey('inventory_stock', $result[$technicianId1][$unitSale11Key]);
        $this->assertArrayHasKey('inventory_stock', $result[$technicianId1][$unitSale12Key]);
        $this->assertArrayHasKey('inventory_stock', $result[$technicianId2][$unitSale21Key]);

        $this->assertEquals($inventory11->stock, $result[$technicianId1][$unitSale11Key]['inventory_stock']);
        $this->assertEquals($inventory12->stock, $result[$technicianId1][$unitSale12Key]['inventory_stock']);
        $this->assertEmpty($result[$technicianId2][$unitSale21Key]['inventory_stock']);

        $this->assertArrayHasKey('inventory_make', $result[$technicianId1][$unitSale11Key]);
        $this->assertArrayHasKey('inventory_make', $result[$technicianId1][$unitSale12Key]);
        $this->assertArrayHasKey('inventory_make', $result[$technicianId2][$unitSale21Key]);

        $this->assertEquals($inventory11->manufacturer, $result[$technicianId1][$unitSale11Key]['inventory_make']);
        $this->assertEquals($inventory12->manufacturer, $result[$technicianId1][$unitSale12Key]['inventory_make']);
        $this->assertEmpty($result[$technicianId2][$unitSale21Key]['inventory_make']);

        $this->assertArrayHasKey('inventory_notes', $result[$technicianId1][$unitSale11Key]);
        $this->assertArrayHasKey('inventory_notes', $result[$technicianId1][$unitSale12Key]);
        $this->assertArrayHasKey('inventory_notes', $result[$technicianId2][$unitSale21Key]);

        $this->assertEquals($inventory11->notes, $result[$technicianId1][$unitSale11Key]['inventory_notes']);
        $this->assertEquals($inventory12->notes, $result[$technicianId1][$unitSale12Key]['inventory_notes']);
        $this->assertEmpty($result[$technicianId2][$unitSale21Key]['inventory_notes']);
    }

    /**
     * @covers ::serviceReport
     *
     * @group DMS
     * @group DMS_SERVICE_ORDER
     *
     * @dataProvider serviceReportProvider
     *
     * @param array $serviceTechnician11
     * @param array $serviceTechnician12
     * @param array $serviceTechnician21
     * @throws BindingResolutionException
     * @throws MissingTestDealerIdException
     */
    public function testServiceReportWithTechnician(array $serviceTechnician11, array $serviceTechnician12, array $serviceTechnician21)
    {
        $unitSaleId11 = factory(UnitSale::class)->create([])->id;
        $unitSaleId12 = factory(UnitSale::class)->create([])->id;
        $unitSaleId21 = factory(UnitSale::class)->create([])->id;

        $technician1 = factory(Technician::class)->create([]);
        $technician2 = factory(Technician::class)->create([]);

        $technicianId1 = $technician1->id;
        $technicianId2 = $technician2->id;

        $this->createServiceItemTechnician($serviceTechnician11, $unitSaleId11, $technicianId1);
        $this->createServiceItemTechnician($serviceTechnician12, $unitSaleId12, $technicianId1);
        $this->createServiceItemTechnician($serviceTechnician21, $unitSaleId21, $technicianId2);

        /** @var ServiceItemTechnicianRepository $repository */
        $repository = app()->make(ServiceItemTechnicianRepository::class);

        $result = $repository->serviceReport([
            'dealer_id' => $this->getTestDealerId(),
            'technician_id' => [$technicianId2],
        ]);

        $this->assertArrayNotHasKey($technicianId1, $result);
        $this->assertArrayHasKey($technicianId2, $result);
    }

    /**
     * @covers ::serviceReport
     *
     * @group DMS
     * @group DMS_SERVICE_ORDER
     *
     * @dataProvider serviceReportProvider
     *
     * @param array $serviceTechnician11
     * @param array $serviceTechnician12
     * @param array $serviceTechnician21
     * @throws BindingResolutionException
     * @throws MissingTestDealerIdException
     */
    public function testServiceReportWithDates(array $serviceTechnician11, array $serviceTechnician12, array $serviceTechnician21)
    {
        $unitSaleId11 = factory(UnitSale::class)->create()->id;
        $unitSaleId12 = factory(UnitSale::class)->create()->id;
        $unitSaleId21 = factory(UnitSale::class)->create()->id;

        $technician1 = factory(Technician::class)->create([]);
        $technician2 = factory(Technician::class)->create([]);

        $technicianId1 = $technician1->id;
        $technicianId2 = $technician2->id;

        $this->createServiceItemTechnician($serviceTechnician11, $unitSaleId11, $technicianId1);
        $this->createServiceItemTechnician($serviceTechnician12, $unitSaleId12, $technicianId1);
        $this->createServiceItemTechnician($serviceTechnician21, $unitSaleId21, $technicianId2);

        /** @var ServiceItemTechnicianRepository $repository */
        $repository = app()->make(ServiceItemTechnicianRepository::class);

        $result = $repository->serviceReport([
            'dealer_id' => $this->getTestDealerId(),
            'from_date' => now()->subWeeks(2)->startOfDay(),
            'to_date' => now()->endOfDay(),
        ]);

        $this->assertArrayHasKey($technicianId1, $result);
        $this->assertArrayHasKey($technicianId2, $result);

        $unitSale11Key = (bool) array_search($unitSaleId11, array_column($result[$technicianId1], 'sale_id'));
        $unitSale12Key = (bool) array_search($unitSaleId12, array_column($result[$technicianId1], 'sale_id'));

        $this->assertFalse($unitSale11Key);
        $this->assertTrue($unitSale12Key);
    }

    /**
     * @covers ::serviceReport
     *
     * @group DMS
     * @group DMS_SERVICE_ORDER
     *
     * @dataProvider serviceReportProvider
     *
     * @param array $serviceTechnician11
     * @param array $serviceTechnician12
     * @param array $serviceTechnician21
     * @throws BindingResolutionException
     * @throws MissingTestDealerIdException
     */
    public function testServiceReportWithTypes(array $serviceTechnician11, array $serviceTechnician12, array $serviceTechnician21)
    {
        $unitSaleId11 = factory(UnitSale::class)->create([])->id;
        $unitSaleId12 = factory(UnitSale::class)->create([])->id;
        $unitSaleId21 = factory(UnitSale::class)->create([])->id;

        $technician1 = factory(Technician::class)->create([]);
        $technician2 = factory(Technician::class)->create([]);

        $technicianId1 = $technician1->id;
        $technicianId2 = $technician2->id;

        $this->createServiceItemTechnician($serviceTechnician11, $unitSaleId11, $technicianId1);
        $this->createServiceItemTechnician($serviceTechnician12, $unitSaleId12, $technicianId1);
        $this->createServiceItemTechnician($serviceTechnician21, $unitSaleId21, $technicianId2);

        /** @var ServiceItemTechnicianRepository $repository */
        $repository = app()->make(ServiceItemTechnicianRepository::class);

        $result = $repository->serviceReport([
            'dealer_id' => $this->getTestDealerId(),
            'repair_order_type' => ['retail']
        ]);

        $this->assertArrayHasKey($technicianId1, $result);
        $this->assertArrayNotHasKey($technicianId2, $result);

        $unitSale11Key = array_search($unitSaleId11, array_column($result[$technicianId1], 'sale_id'));
        $unitSale12Key = array_search($unitSaleId12, array_column($result[$technicianId1], 'sale_id'));

        $this->assertFalse($unitSale11Key);
        $this->assertNotFalse($unitSale12Key);
    }

    public function serviceReportProvider(): array
    {
        return [[
            [
                'act_hrs' => 123.00,
                'paid_hrs' => 111.00,
                'billed_hrs' => 100.00,
                'invoice_total' => 999.00,
                'doc_num' => 'test11',
                'sale_date' => (new \DateTime())->modify('-1 day'),
                'sales_person_id' => 111,
                'customer_name' => 'test_customer_name147',
                'created_at' => (new \DateTime())->modify('-1 day'),
                'closed_at' => (new \DateTime())->modify('-1 day'),
                'repair_order_type' => 'internal',
                'paid_retail' => 654,
                'qb_invoice_items' => [
                    [
                        'cost' => 44.00,
                        'type' => 'trailer',
                        'unit_price' => 0.00,
                        'qty' => 0,
                    ],
                    [
                        'cost' => 55.00,
                        'type' => 'deposit_down_payment',
                        'unit_price' => 0.00,
                        'qty' => 0,
                    ]
                ]
            ],
            [
                'act_hrs' => 456.00,
                'paid_hrs' => 112.00,
                'billed_hrs' => 200.00,
                'invoice_total' => 888.00,
                'doc_num' => 'test12',
                'sale_date' => (new \DateTime())->modify('-1 week'),
                'sales_person_id' => 333,
                'customer_name' => 'test_customer_name258',
                'created_at' => (new \DateTime())->modify('-1 week'),
                'closed_at' => (new \DateTime())->modify('-1 week'),
                'repair_order_type' => 'retail',
                'paid_retail' => 789,
                'qb_invoice_items' => [
                    [
                        'cost' => 3256.00,
                        'type' => 'trailer',
                        'unit_price' => 0.00,
                        'qty' => 0,
                    ],
                    [
                        'cost' => 78.00,
                        'type' => 'labor',
                        'unit_price' => 15.00,
                        'qty' => 2,
                    ],
                    [
                        'cost' => 91.00,
                        'type' => 'labor',
                        'unit_price' => 16.00,
                        'qty' => 3,
                    ],
                ]
            ],
            [
                'act_hrs' => 789.00,
                'paid_hrs' => 113.00,
                'billed_hrs' => 300.00,
                'invoice_total' => 777.00,
                'doc_num' => 'test21',
                'sale_date' => (new \DateTime())->modify('-1 month'),
                'sales_person_id' => 444,
                'customer_name' => 'test_customer_name369',
                'created_at' => (new \DateTime())->modify('-1 month'),
                'closed_at' => (new \DateTime())->modify('-1 month'),
                'repair_order_type' => 'warranty',
                'paid_retail' => 123,
                'qb_invoice_items' => [
                    [
                        'cost' => 222.00,
                        'unit_price' => 12.00,
                        'qty' => 10,
                        'type' => 'part',
                    ],
                    [
                        'cost' => 333.00,
                        'unit_price' => 13.00,
                        'qty' => 20,
                        'type' => 'part',
                    ],
                    [
                        'cost' => 444.00,
                        'unit_price' => 14.00,
                        'qty' => 30,
                        'type' => 'part',
                    ],
                ]
            ]
        ]];
    }

    /**
     * @param array $serviceTechnician
     * @param int|null $unitSaleId
     * @param int $technicianId
     */
    private function createServiceItemTechnician(array $serviceTechnician, ?int $unitSaleId, int $technicianId): void
    {
        $serviceOrder = factory(ServiceOrder::class)->create([
            'unit_sale_id' => $unitSaleId,
            'type' => $serviceTechnician['repair_order_type'],
            'created_at' => $serviceTechnician['created_at'],
            'closed_at' => $serviceTechnician['closed_at'],
        ]);

        $serviceItem = factory(ServiceItem::class)->create([
            'repair_order_id' => $serviceOrder->id,
            'amount' => $serviceTechnician['paid_retail']
        ]);

        factory(ServiceItemTechnician::class)->create([
            'service_item_id' => $serviceItem->id,
            'dms_settings_technician_id' => $technicianId,
            'act_hrs' => $serviceTechnician['act_hrs'],
            'billed_hrs' => $serviceTechnician['billed_hrs'],
            'paid_hrs' => $serviceTechnician['paid_hrs'],
            'completed_date' => now(),
        ]);
    }
}
