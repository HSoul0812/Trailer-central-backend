<?php

namespace Tests\Integration\Repositories\Dms;

use App\Models\CRM\Account\Invoice;
use App\Models\CRM\Account\InvoiceItem;
use App\Models\CRM\Account\Payment;
use App\Models\CRM\Dms\PaymentLabor;
use App\Models\CRM\Dms\Quickbooks\Item;
use App\Models\CRM\Dms\UnitSaleLabor;
use App\Models\CRM\User\Customer;
use App\Models\Inventory\Inventory;
use App\Repositories\Dms\UnitSaleLaborRepository;
use App\Models\CRM\Dms\UnitSale;
use App\Models\User\DealerLocation;
use App\Models\User\User;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Class UnitSaleLaborRepositoryTest
 * @package Tests\Integration\Repositories\Dms
 *
 * @coversDefaultClass \App\Repositories\Dms\UnitSaleLaborRepository
 */
class UnitSaleLaborRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    private $dealerId;

    public function setUp(): void
    {
        parent::setUp();
        $this->dealerId = factory(User::class)->create()->dealer_id;
    }

    public function tearDown(): void
    {
        User::where('dealer_id', $this->dealerId)->delete();
        Inventory::where('dealer_id', $this->dealerId)->delete();
        UnitSale::where('dealer_id', $this->dealerId)->delete();
        Invoice::where('dealer_id', $this->dealerId)->delete();
        Payment::where('dealer_id', $this->dealerId)->delete();
        $this->dealerId = null;
        parent::tearDown();
    }

    /**
     * @covers ::getTechnicians
     *
     * @group DMS
     * @group DMS_UNIT_SALE_LABOR
     */
    public function testGetTechnicians()
    {
        $params = [
            'dealer_id' => $this->getTestDealerId()
        ];

        $technician1 = 'unit_test_get_technician_technician_1';
        $technician2 = 'unit_test_get_technician_technician_2';
        $technician3 = 'unit_test_get_technician_technician_3';

        for ($i = 0; $i < 3; $i++) {
            factory(UnitSaleLabor::class)->create([
                'technician' => $technician1,
                'unit_sale_id' => factory(UnitSale::class)->create($params)->getKey()
            ]);
        }

        factory(UnitSaleLabor::class)->create([
            'technician' => $technician2,
            'unit_sale_id' => factory(UnitSale::class)->create($params)->getKey()
        ]);

        /** @var UnitSaleLaborRepository $repository */
        $repository = app()->make(UnitSaleLaborRepository::class);
        $result = $repository->getTechnicians($params);

        $this->assertIsArray($result);

        $this->assertContains($technician1, $result);
        $this->assertContains($technician2, $result);

        $this->assertCount(1, array_intersect($result, [$technician1]));
        $this->assertCount(1, array_intersect($result, [$technician2]));

        $this->assertNotContains($technician3, $result);
    }

    /**
     * @covers ::serviceReport
     * @dataProvider serviceReportProvider
     *
     * @group DMS
     * @group DMS_UNIT_SALE_LABOR
     *
     * @param array $unitSaleLabor11
     * @param array $unitSaleLabor12
     * @param array $unitSaleLabor21
     * @throws BindingResolutionException
     */
    public function testServiceReport(array $unitSaleLabor11, array $unitSaleLabor12, array $unitSaleLabor21)
    {
        $technician1 = 'unit_test_service_report_technician_1';
        $technician2 = 'unit_test_service_report_technician_2';

        $customerId11 = factory(Customer::class)->create([
            'display_name' => $unitSaleLabor11['customer_name'],
        ])->id;

        $customerId12 = factory(Customer::class)->create([
            'display_name' => $unitSaleLabor12['customer_name'],
        ])->id;

        $customerId21 = factory(Customer::class)->create([
            'display_name' => $unitSaleLabor21['customer_name'],
        ])->id;

        $inventory11 = factory(Inventory::class)->create([
            'dealer_id' => $this->dealerId,
            'notes' => 'inventory11'
        ]);

        $inventory12 = factory(Inventory::class)->create([
            'dealer_id' => $this->dealerId,
            'notes' => 'inventory12'
        ]);

        $unitSaleId11 = factory(UnitSale::class)->create(
            [
                'sales_person_id' => $unitSaleLabor11['sales_person_id'],
                'buyer_id' => $customerId11,
                'dealer_id' => $this->dealerId,
                'inventory_id' => $inventory11->inventory_id
            ]
        )->id;

        $unitSaleId12 = factory(UnitSale::class)->create(
            [
                'sales_person_id' => $unitSaleLabor12['sales_person_id'],
                'buyer_id' => $customerId12,
                'dealer_id' => $this->dealerId,
                'inventory_id' => $inventory12->inventory_id
            ]
        )->id;

        $unitSaleId21 = factory(UnitSale::class)->create(
            [
                'sales_person_id' => $unitSaleLabor21['sales_person_id'],
                'dealer_id' => $this->dealerId,
                'buyer_id' => $customerId21,
            ]
        )->id;

        $invoiceId11 = factory(Invoice::class)->create(
            [
                'unit_sale_id' => $unitSaleId11,
                'dealer_id' => $this->dealerId,
                'total' => $unitSaleLabor11['invoice_total'],
                'doc_num' => $unitSaleLabor11['doc_num'],
                'invoice_date' => $unitSaleLabor11['sale_date'],
            ]
        )->id;

        $invoiceId12 = factory(Invoice::class)->create(
            [
                'unit_sale_id' => $unitSaleId12,
                'dealer_id' => $this->dealerId,
                'total' => $unitSaleLabor12['invoice_total'],
                'doc_num' => $unitSaleLabor12['doc_num'],
                'invoice_date' => $unitSaleLabor12['sale_date'],
            ]
        )->id;

        $invoiceId21 = factory(Invoice::class)->create(
            [
                'unit_sale_id' => $unitSaleId21,
                'dealer_id' => $this->dealerId,
                'total' => $unitSaleLabor21['invoice_total'],
                'doc_num' => $unitSaleLabor21['doc_num'],
                'invoice_date' => $unitSaleLabor21['sale_date'],
            ]
        )->id;

        $invoices = [$invoiceId11 => $unitSaleLabor11, $invoiceId12 => $unitSaleLabor12, $invoiceId21 => $unitSaleLabor21];

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

        factory(UnitSaleLabor::class)->create([
            'technician' => $technician1,
            'unit_sale_id' => $unitSaleId11,
            'actual_hours' => $unitSaleLabor11['actual_hours'],
            'paid_hours' => $unitSaleLabor11['paid_hours'],
            'billed_hours' => $unitSaleLabor11['billed_hours'],
        ]);

        factory(UnitSaleLabor::class)->create([
            'technician' => $technician1,
            'unit_sale_id' => $unitSaleId12,
            'actual_hours' => $unitSaleLabor12['actual_hours'],
            'paid_hours' => $unitSaleLabor12['paid_hours'],
            'billed_hours' => $unitSaleLabor12['billed_hours'],
        ]);

        factory(UnitSaleLabor::class)->create([
            'technician' => $technician2,
            'unit_sale_id' => $unitSaleId21,
            'actual_hours' => $unitSaleLabor21['actual_hours'],
            'paid_hours' => $unitSaleLabor21['paid_hours'],
            'billed_hours' => $unitSaleLabor21['billed_hours'],
        ]);

        /** @var UnitSaleLaborRepository $repository */
        $repository = app()->make(UnitSaleLaborRepository::class);

        $result = $repository->serviceReport(['dealer_id' => $this->dealerId]);

        $this->assertArrayHasKey($technician1, $result);
        $this->assertArrayHasKey($technician2, $result);

        $unitSale11Key = array_search($unitSaleId11, array_column($result[$technician1], 'sale_id'));
        $unitSale12Key = array_search($unitSaleId12, array_column($result[$technician1], 'sale_id'));
        $unitSale21Key = array_search($unitSaleId21, array_column($result[$technician2], 'sale_id'));

        $notExistingKey = array_search($unitSaleId11, array_column($result[$technician2], 'sale_id'));

        $this->assertNotFalse($unitSale11Key);
        $this->assertNotFalse($unitSale12Key);
        $this->assertNotFalse($unitSale21Key);

        $this->assertFalse($notExistingKey);

        $this->assertArrayHasKey('actual_hours', $result[$technician1][$unitSale11Key]);
        $this->assertArrayHasKey('actual_hours', $result[$technician1][$unitSale12Key]);
        $this->assertArrayHasKey('actual_hours', $result[$technician2][$unitSale21Key]);

        $this->assertEquals($unitSaleLabor11['actual_hours'], $result[$technician1][$unitSale11Key]['actual_hours']);
        $this->assertEquals($unitSaleLabor12['actual_hours'], $result[$technician1][$unitSale12Key]['actual_hours']);
        $this->assertEquals($unitSaleLabor21['actual_hours'], $result[$technician2][$unitSale21Key]['actual_hours']);

        $this->assertArrayHasKey('paid_hours', $result[$technician1][$unitSale11Key]);
        $this->assertArrayHasKey('paid_hours', $result[$technician1][$unitSale12Key]);
        $this->assertArrayHasKey('paid_hours', $result[$technician2][$unitSale21Key]);

        $this->assertEquals($unitSaleLabor11['paid_hours'], $result[$technician1][$unitSale11Key]['paid_hours']);
        $this->assertEquals($unitSaleLabor12['paid_hours'], $result[$technician1][$unitSale12Key]['paid_hours']);
        $this->assertEquals($unitSaleLabor21['paid_hours'], $result[$technician2][$unitSale21Key]['paid_hours']);

        $this->assertArrayHasKey('billed_hours', $result[$technician1][$unitSale11Key]);
        $this->assertArrayHasKey('billed_hours', $result[$technician1][$unitSale12Key]);
        $this->assertArrayHasKey('billed_hours', $result[$technician2][$unitSale21Key]);

        $this->assertEquals($unitSaleLabor11['billed_hours'], $result[$technician1][$unitSale11Key]['billed_hours']);
        $this->assertEquals($unitSaleLabor12['billed_hours'], $result[$technician1][$unitSale12Key]['billed_hours']);
        $this->assertEquals($unitSaleLabor21['billed_hours'], $result[$technician2][$unitSale21Key]['billed_hours']);

        $this->assertArrayHasKey('technician', $result[$technician1][$unitSale11Key]);
        $this->assertArrayHasKey('technician', $result[$technician1][$unitSale12Key]);
        $this->assertArrayHasKey('technician', $result[$technician2][$unitSale21Key]);

        $this->assertEquals($technician1, $result[$technician1][$unitSale11Key]['technician']);
        $this->assertEquals($technician1, $result[$technician1][$unitSale12Key]['technician']);
        $this->assertEquals($technician2, $result[$technician2][$unitSale21Key]['technician']);

        $this->assertArrayHasKey('invoice_id', $result[$technician1][$unitSale11Key]);
        $this->assertArrayHasKey('invoice_id', $result[$technician1][$unitSale12Key]);
        $this->assertArrayHasKey('invoice_id', $result[$technician2][$unitSale21Key]);

        $this->assertEquals($invoiceId11, $result[$technician1][$unitSale11Key]['invoice_id']);
        $this->assertEquals($invoiceId12, $result[$technician1][$unitSale12Key]['invoice_id']);
        $this->assertEquals($invoiceId21, $result[$technician2][$unitSale21Key]['invoice_id']);

        $this->assertArrayHasKey('invoice_total', $result[$technician1][$unitSale11Key]);
        $this->assertArrayHasKey('invoice_total', $result[$technician1][$unitSale12Key]);
        $this->assertArrayHasKey('invoice_total', $result[$technician2][$unitSale21Key]);

        $this->assertEquals($unitSaleLabor11['invoice_total'], $result[$technician1][$unitSale11Key]['invoice_total']);
        $this->assertEquals($unitSaleLabor12['invoice_total'], $result[$technician1][$unitSale12Key]['invoice_total']);
        $this->assertEquals($unitSaleLabor21['invoice_total'], $result[$technician2][$unitSale21Key]['invoice_total']);

        $this->assertArrayHasKey('doc_num', $result[$technician1][$unitSale11Key]);
        $this->assertArrayHasKey('doc_num', $result[$technician1][$unitSale12Key]);
        $this->assertArrayHasKey('doc_num', $result[$technician2][$unitSale21Key]);

        $this->assertEquals($unitSaleLabor11['doc_num'], $result[$technician1][$unitSale11Key]['doc_num']);
        $this->assertEquals($unitSaleLabor12['doc_num'], $result[$technician1][$unitSale12Key]['doc_num']);
        $this->assertEquals($unitSaleLabor21['doc_num'], $result[$technician2][$unitSale21Key]['doc_num']);

        $this->assertArrayHasKey('sale_date', $result[$technician1][$unitSale11Key]);
        $this->assertArrayHasKey('sale_date', $result[$technician1][$unitSale12Key]);
        $this->assertArrayHasKey('sale_date', $result[$technician2][$unitSale21Key]);

        $this->assertEquals($unitSaleLabor11['sale_date']->format('Y-m-d'), $result[$technician1][$unitSale11Key]['sale_date']);
        $this->assertEquals($unitSaleLabor12['sale_date']->format('Y-m-d'), $result[$technician1][$unitSale12Key]['sale_date']);
        $this->assertEquals($unitSaleLabor21['sale_date']->format('Y-m-d'), $result[$technician2][$unitSale21Key]['sale_date']);

        $this->assertArrayHasKey('sales_person_id', $result[$technician1][$unitSale11Key]);
        $this->assertArrayHasKey('sales_person_id', $result[$technician1][$unitSale12Key]);
        $this->assertArrayHasKey('sales_person_id', $result[$technician2][$unitSale21Key]);

        $this->assertEquals($unitSaleLabor11['sales_person_id'], $result[$technician1][$unitSale11Key]['sales_person_id']);
        $this->assertEquals($unitSaleLabor12['sales_person_id'], $result[$technician1][$unitSale12Key]['sales_person_id']);
        $this->assertEquals($unitSaleLabor21['sales_person_id'], $result[$technician2][$unitSale21Key]['sales_person_id']);

        $this->assertArrayHasKey('customer_name', $result[$technician1][$unitSale11Key]);
        $this->assertArrayHasKey('customer_name', $result[$technician1][$unitSale12Key]);
        $this->assertArrayHasKey('customer_name', $result[$technician2][$unitSale21Key]);

        $this->assertEquals($unitSaleLabor11['customer_name'], $result[$technician1][$unitSale11Key]['customer_name']);
        $this->assertEquals($unitSaleLabor12['customer_name'], $result[$technician1][$unitSale12Key]['customer_name']);
        $this->assertEquals($unitSaleLabor21['customer_name'], $result[$technician2][$unitSale21Key]['customer_name']);

        $this->assertArrayHasKey('unit_sale_amount', $result[$technician1][$unitSale11Key]);
        $this->assertArrayHasKey('unit_sale_amount', $result[$technician1][$unitSale12Key]);
        $this->assertArrayHasKey('unit_sale_amount', $result[$technician2][$unitSale21Key]);

        $this->assertEquals($unitSaleLabor11['invoice_total'], $result[$technician1][$unitSale11Key]['unit_sale_amount']);
        $this->assertEquals($unitSaleLabor12['invoice_total'], $result[$technician1][$unitSale12Key]['unit_sale_amount']);
        $this->assertEmpty($result[$technician2][$unitSale21Key]['unit_sale_amount']);

        $expectedUnitCostAmount11 = array_sum(array_column($unitSaleLabor11['qb_invoice_items'], 'cost'));

        $expectedUnitCostAmount12 = array_sum(array_column(array_filter($unitSaleLabor12['qb_invoice_items'], function ($item) {
            return $item['type'] === 'trailer';
        }), 'cost'));

        $this->assertArrayHasKey('unit_cost_amount', $result[$technician1][$unitSale11Key]);
        $this->assertArrayHasKey('unit_cost_amount', $result[$technician1][$unitSale12Key]);
        $this->assertArrayHasKey('unit_cost_amount', $result[$technician2][$unitSale21Key]);

        $this->assertEquals($expectedUnitCostAmount11, $result[$technician1][$unitSale11Key]['unit_cost_amount']);
        $this->assertEquals($expectedUnitCostAmount12, $result[$technician1][$unitSale12Key]['unit_cost_amount']);
        $this->assertEmpty($result[$technician2][$unitSale21Key]['unit_cost_amount']);

        $expectedPartSaleAmount21 = array_reduce($unitSaleLabor21['qb_invoice_items'], function ($total, $invoiceItem) {
            $total += $invoiceItem['unit_price'] * $invoiceItem['qty'];
            return $total;
        });

        $this->assertArrayHasKey('part_sale_amount', $result[$technician1][$unitSale11Key]);
        $this->assertArrayHasKey('part_sale_amount', $result[$technician1][$unitSale12Key]);
        $this->assertArrayHasKey('part_sale_amount', $result[$technician2][$unitSale21Key]);

        $this->assertEmpty($result[$technician1][$unitSale11Key]['part_sale_amount']);
        $this->assertEmpty($result[$technician1][$unitSale12Key]['part_sale_amount']);
        $this->assertEquals($expectedPartSaleAmount21, $result[$technician2][$unitSale21Key]['part_sale_amount']);

        $expectedPartCostAmount21 = array_sum(array_column($unitSaleLabor21['qb_invoice_items'], 'cost'));

        $this->assertArrayHasKey('part_cost_amount', $result[$technician1][$unitSale11Key]);
        $this->assertArrayHasKey('part_cost_amount', $result[$technician1][$unitSale12Key]);
        $this->assertArrayHasKey('part_cost_amount', $result[$technician2][$unitSale21Key]);

        $this->assertEmpty($result[$technician1][$unitSale11Key]['part_cost_amount']);
        $this->assertEmpty($result[$technician1][$unitSale12Key]['part_cost_amount']);
        $this->assertEquals($expectedPartCostAmount21, $result[$technician2][$unitSale21Key]['part_cost_amount']);

        $expectedLaborSaleAmount12 = array_reduce($unitSaleLabor12['qb_invoice_items'], function ($total, $invoiceItem) {
            $total += $invoiceItem['unit_price'] * $invoiceItem['qty'];
            return $total;
        });

        $this->assertArrayHasKey('labor_sale_amount', $result[$technician1][$unitSale11Key]);
        $this->assertArrayHasKey('labor_sale_amount', $result[$technician1][$unitSale12Key]);
        $this->assertArrayHasKey('labor_sale_amount', $result[$technician2][$unitSale21Key]);

        $this->assertEmpty($result[$technician1][$unitSale11Key]['labor_sale_amount']);
        $this->assertEquals($expectedLaborSaleAmount12, $result[$technician1][$unitSale12Key]['labor_sale_amount']);
        $this->assertEmpty($result[$technician2][$unitSale21Key]['labor_sale_amount']);

        $expectedLaborCostAmount12 = array_sum(array_column(array_filter($unitSaleLabor12['qb_invoice_items'], function ($item) {
            return $item['type'] === 'labor';
        }), 'cost'));

        $this->assertArrayHasKey('labor_cost_amount', $result[$technician1][$unitSale11Key]);
        $this->assertArrayHasKey('labor_cost_amount', $result[$technician1][$unitSale12Key]);
        $this->assertArrayHasKey('labor_cost_amount', $result[$technician2][$unitSale21Key]);

        $this->assertEmpty($result[$technician1][$unitSale11Key]['labor_cost_amount']);
        $this->assertEquals($expectedLaborCostAmount12, $result[$technician1][$unitSale12Key]['labor_cost_amount']);
        $this->assertEmpty($result[$technician2][$unitSale21Key]['labor_cost_amount']);

        $this->assertArrayHasKey('inventory_stock', $result[$technician1][$unitSale11Key]);
        $this->assertArrayHasKey('inventory_stock', $result[$technician1][$unitSale12Key]);
        $this->assertArrayHasKey('inventory_stock', $result[$technician2][$unitSale21Key]);

        $this->assertEquals($inventory11->stock, $result[$technician1][$unitSale11Key]['inventory_stock']);
        $this->assertEquals($inventory12->stock, $result[$technician1][$unitSale12Key]['inventory_stock']);

        $this->assertArrayHasKey('inventory_make', $result[$technician1][$unitSale11Key]);
        $this->assertArrayHasKey('inventory_make', $result[$technician1][$unitSale12Key]);
        $this->assertArrayHasKey('inventory_make', $result[$technician2][$unitSale21Key]);

        $this->assertEquals($inventory11->manufacturer, $result[$technician1][$unitSale11Key]['inventory_make']);
        $this->assertEquals($inventory12->manufacturer, $result[$technician1][$unitSale12Key]['inventory_make']);


        $this->assertArrayHasKey('inventory_notes', $result[$technician1][$unitSale11Key]);
        $this->assertArrayHasKey('inventory_notes', $result[$technician1][$unitSale12Key]);
        $this->assertArrayHasKey('inventory_notes', $result[$technician2][$unitSale21Key]);

        $this->assertEquals($inventory11->notes, $result[$technician1][$unitSale11Key]['inventory_notes']);
        $this->assertEquals($inventory12->notes, $result[$technician1][$unitSale12Key]['inventory_notes']);

    }

    /**
     * @covers ::serviceReport
     * @dataProvider serviceReportProvider
     *
     * @group DMS
     * @group DMS_UNIT_SALE_LABOR
     *
     * @param array $unitSaleLabor11
     * @param array $unitSaleLabor12
     * @param array $unitSaleLabor21
     * @throws BindingResolutionException
     */
    public function testServiceReportWithDates(array $unitSaleLabor11, array $unitSaleLabor12, array $unitSaleLabor21)
    {
        $technician1 = 'unit_test_service_report_technician_1';
        $technician2 = 'unit_test_service_report_technician_2';

        $unitSaleId11 = factory(UnitSale::class)->create([
            'dealer_id' => $this->dealerId,
            'created_at' => $unitSaleLabor11['created_at']
        ])->id;

        $unitSaleId12 = factory(UnitSale::class)->create([
            'dealer_id' => $this->dealerId,
            'created_at' => $unitSaleLabor12['created_at']
        ])->id;

        $unitSaleId21 = factory(UnitSale::class)->create([
            'dealer_id' => $this->dealerId,
            'created_at' => $unitSaleLabor21['created_at']
        ])->id;

        factory(UnitSaleLabor::class)->create([
            'technician' => $technician1,
            'unit_sale_id' => $unitSaleId11,
        ]);

        factory(UnitSaleLabor::class)->create([
            'technician' => $technician1,
            'unit_sale_id' => $unitSaleId12,
        ]);

        factory(UnitSaleLabor::class)->create([
            'technician' => $technician2,
            'unit_sale_id' => $unitSaleId21,
        ]);

        /** @var UnitSaleLaborRepository $repository */
        $repository = app()->make(UnitSaleLaborRepository::class);

        $result = $repository->serviceReport(
            [
                'dealer_id' => $this->dealerId,
                'from_date' => (new \DateTime)->modify('-2 weeks')->format('Y-m-d'),
                'to_date' => (new \DateTime)->modify('-1 week')->modify('+1 day')->format('Y-m-d')
            ]
        );

        $this->assertArrayHasKey($technician1, $result);
        $this->assertArrayNotHasKey($technician2, $result);

        $unitSale11Key = array_search($unitSaleId11, array_column($result[$technician1], 'sale_id'));
        $unitSale12Key = array_search($unitSaleId12, array_column($result[$technician1], 'sale_id'));

        $this->assertFalse($unitSale11Key);
        $this->assertNotFalse($unitSale12Key);
    }

    /**
     * @covers ::serviceReport
     *
     * @group DMS
     * @group DMS_UNIT_SALE_LABOR
     */
    public function testServiceReportWithTechnician()
    {
        $technician1 = 'unit_test_service_report_technician_1';
        $technician2 = 'unit_test_service_report_technician_2';

        $unitSaleId11 = factory(UnitSale::class)->create([
            'dealer_id' => $this->dealerId
        ])->id;

        $unitSaleId12 = factory(UnitSale::class)->create([
            'dealer_id' => $this->dealerId,
        ])->id;

        $unitSaleId21 = factory(UnitSale::class)->create([
            'dealer_id' => $this->dealerId,
        ])->id;

        factory(UnitSaleLabor::class)->create([
            'technician' => $technician1,
            'unit_sale_id' => $unitSaleId11,
        ]);

        factory(UnitSaleLabor::class)->create([
            'technician' => $technician1,
            'unit_sale_id' => $unitSaleId12,
        ]);

        factory(UnitSaleLabor::class)->create([
            'technician' => $technician2,
            'unit_sale_id' => $unitSaleId21,
        ]);

        /** @var UnitSaleLaborRepository $repository */
        $repository = app()->make(UnitSaleLaborRepository::class);

        $result = $repository->serviceReport([
            'dealer_id' => $this->dealerId,
            'technician' => [$technician2],
        ]);

        $this->assertArrayNotHasKey($technician1, $result);
        $this->assertArrayHasKey($technician2, $result);
    }

    public function serviceReportProvider(): array
    {
        return [
            [
                [
                    'actual_hours' => 123.00,
                    'paid_hours' => 111.00,
                    'billed_hours' => 100.00,
                    'invoice_total' => 999.00,
                    'doc_num' => 'test11',
                    'sale_date' => (new \DateTime())->modify('-1 day'),
                    'sales_person_id' => 111,
                    'customer_name' => 'test_customer_name147',
                    'created_at' => (new \DateTime())->modify('-1 day'),
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
                    'actual_hours' => 456.00,
                    'paid_hours' => 112.00,
                    'billed_hours' => 200.00,
                    'invoice_total' => 888.00,
                    'doc_num' => 'test12',
                    'sale_date' => (new \DateTime())->modify('-1 week'),
                    'sales_person_id' => 333,
                    'customer_name' => 'test_customer_name258',
                    'created_at' => (new \DateTime())->modify('-1 week'),
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
                    'actual_hours' => 789.00,
                    'paid_hours' => 113.00,
                    'billed_hours' => 300.00,
                    'invoice_total' => 777.00,
                    'doc_num' => 'test21',
                    'sale_date' => (new \DateTime())->modify('-1 month'),
                    'sales_person_id' => 444,
                    'customer_name' => 'test_customer_name369',
                    'created_at' => (new \DateTime())->modify('-1 month'),
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
            ]
        ];
    }


    /**
     * @covers ::serviceReport
     * @dataProvider serviceReportPaymentLaborProvider
     *
     * @group DMS
     * @group DMS_UNIT_SALE_LABOR
     *
     * @param array $paymentLabor31
     * @param array $paymentLabor32
     * @throws BindingResolutionException
     */
    public function testServiceReportPaymentLabor(
        array $paymentLabor31,
        array $paymentLabor32
    )
    {
        $technician3 = 'unit_test_service_report_technician_3';
        $technician4 = 'unit_test_service_report_technician_4';

        $customerId31 = factory(Customer::class)->create(
            [
                'display_name' => $paymentLabor31['customer_name'],
            ]
        )->id;

        $customerId32 = factory(Customer::class)->create(
            [
                'display_name' => $paymentLabor32['customer_name'],
            ]
        )->id;

        $invoiceId31 = factory(Invoice::class)->create(
            [
                'dealer_id' => $this->dealerId,
                'unit_sale_id' => null,
                'total' => $paymentLabor31['invoice_total'],
                'doc_num' => $paymentLabor31['doc_num'],
                'invoice_date' => $paymentLabor31['sale_date'],
                'customer_id' => $customerId31,
            ]
        )->id;

        $invoiceId32 = factory(Invoice::class)->create(
            [
                'unit_sale_id' => null,
                'dealer_id' => $this->dealerId,
                'total' => $paymentLabor32['invoice_total'],
                'doc_num' => $paymentLabor32['doc_num'],
                'invoice_date' => $paymentLabor32['sale_date'],
                'customer_id' => $customerId32,
            ]
        )->id;

        $paymentId31 = factory(Payment::class)->create(
            [
                'invoice_id' => $invoiceId31,
                'dealer_id' => $this->dealerId,
                'created_at' => $paymentLabor31['created_at'],
            ]
        );

        $paymentId32 = factory(Payment::class)->create(
            [
                'invoice_id' => $invoiceId32,
                'dealer_id' => $this->dealerId,
                'created_at' => $paymentLabor32['created_at'],
            ]
        );

        factory(PaymentLabor::class)->create(
            [
                'payment_id' => $paymentId31,
                'technician' => $technician3,
                'actual_hours' => $paymentLabor31['actual_hours'],
                'paid_hours' => $paymentLabor31['paid_hours'],
                'billed_hours' => $paymentLabor31['billed_hours'],
                'quantity' => $paymentLabor31['quantity'],
                'unit_price' => $paymentLabor31['unit_price'],
                'dealer_cost' => $paymentLabor31['dealer_cost'],
            ]
        );

        factory(PaymentLabor::class)->create(
            [
                'payment_id' => $paymentId32,
                'technician' => $technician4,
                'actual_hours' => $paymentLabor32['actual_hours'],
                'paid_hours' => $paymentLabor32['paid_hours'],
                'billed_hours' => $paymentLabor32['billed_hours'],
                'quantity' => $paymentLabor32['quantity'],
                'unit_price' => $paymentLabor32['unit_price'],
                'dealer_cost' => $paymentLabor32['dealer_cost'],
            ]
        );

        /** @var UnitSaleLaborRepository $repository */
        $repository = app()->make(UnitSaleLaborRepository::class);
        $result = $repository->serviceReport(['dealer_id' => $this->dealerId]);

        $this->assertArrayHasKey($technician3, $result);
        $this->assertArrayHasKey($technician4, $result);

        $this->assertEquals(1,count($result[$technician3]));
        $this->assertEquals(1,count($result[$technician4]));

        $this->assertEquals($paymentLabor31['actual_hours'], $result[$technician3][0]['actual_hours']);
        $this->assertEquals($paymentLabor31['paid_hours'], $result[$technician3][0]['paid_hours']);
        $this->assertEquals($paymentLabor31['billed_hours'], $result[$technician3][0]['billed_hours']);
        $this->assertEquals($paymentLabor31['quantity'] * $paymentLabor31['unit_price'], $result[$technician3][0]['labor_sale_amount']);
        $this->assertEquals($paymentLabor31['quantity'] * $paymentLabor31['dealer_cost'], $result[$technician3][0]['labor_cost_amount']);

        $this->assertEquals($paymentLabor32['actual_hours'], $result[$technician4][0]['actual_hours']);
        $this->assertEquals($paymentLabor32['paid_hours'], $result[$technician4][0]['paid_hours']);
        $this->assertEquals($paymentLabor32['billed_hours'], $result[$technician4][0]['billed_hours']);
        $this->assertEquals($paymentLabor32['quantity'] * $paymentLabor32['unit_price'], $result[$technician4][0]['labor_sale_amount']);
        $this->assertEquals($paymentLabor32['quantity'] * $paymentLabor32['dealer_cost'], $result[$technician4][0]['labor_cost_amount']);
    }

    /**
     * @covers ::serviceReport
     * @dataProvider serviceReportPaymentLaborProvider
     *
     * @group DMS
     * @group DMS_UNIT_SALE_LABOR
     *
     * @param array $paymentLabor31
     * @param array $paymentLabor32
     * @throws BindingResolutionException
     */
    public function testServiceReportPaymentLaborWithDate(
        array $paymentLabor31,
        array $paymentLabor32
    )
    {
        $technician3 = 'unit_test_service_report_technician_3';
        $technician4 = 'unit_test_service_report_technician_4';

        $customerId31 = factory(Customer::class)->create(
            [
                'display_name' => $paymentLabor31['customer_name'],
            ]
        )->id;

        $customerId32 = factory(Customer::class)->create(
            [
                'display_name' => $paymentLabor32['customer_name'],
            ]
        )->id;

        $invoiceId31 = factory(Invoice::class)->create(
            [
                'dealer_id' => $this->dealerId,
                'unit_sale_id' => null,
                'total' => $paymentLabor31['invoice_total'],
                'doc_num' => $paymentLabor31['doc_num'],
                'invoice_date' => $paymentLabor31['sale_date'],
                'customer_id' => $customerId31,
            ]
        )->id;

        $invoiceId32 = factory(Invoice::class)->create(
            [
                'unit_sale_id' => null,
                'dealer_id' => $this->dealerId,
                'total' => $paymentLabor32['invoice_total'],
                'doc_num' => $paymentLabor32['doc_num'],
                'invoice_date' => $paymentLabor32['sale_date'],
                'customer_id' => $customerId32,
            ]
        )->id;

        $paymentId31 = factory(Payment::class)->create(
            [
                'invoice_id' => $invoiceId31,
                'dealer_id' => $this->dealerId,
                'created_at' => $paymentLabor31['created_at'],
            ]
        );

        $paymentId32 = factory(Payment::class)->create(
            [
                'invoice_id' => $invoiceId32,
                'dealer_id' => $this->dealerId,
                'created_at' => $paymentLabor32['created_at'],
            ]
        );

        factory(PaymentLabor::class)->create(
            [
                'payment_id' => $paymentId31,
                'technician' => $technician3,
                'actual_hours' => $paymentLabor31['actual_hours'],
                'paid_hours' => $paymentLabor31['paid_hours'],
                'billed_hours' => $paymentLabor31['billed_hours'],
                'quantity' => $paymentLabor31['quantity'],
                'unit_price' => $paymentLabor31['unit_price'],
                'dealer_cost' => $paymentLabor31['dealer_cost'],
            ]
        );

        factory(PaymentLabor::class)->create(
            [
                'payment_id' => $paymentId32,
                'technician' => $technician4,
                'actual_hours' => $paymentLabor32['actual_hours'],
                'paid_hours' => $paymentLabor32['paid_hours'],
                'billed_hours' => $paymentLabor32['billed_hours'],
                'quantity' => $paymentLabor32['quantity'],
                'unit_price' => $paymentLabor32['unit_price'],
                'dealer_cost' => $paymentLabor32['dealer_cost'],
            ]
        );

        /** @var UnitSaleLaborRepository $repository */
        $repository = app()->make(UnitSaleLaborRepository::class);
        $result = $repository->serviceReport(
            [
                'dealer_id' => $this->dealerId,
                'from_date' => (new \DateTime())->modify('-2 week')->format('Y-m-d'),
                'to_date' => (new \DateTime())->modify('+1 day')->format('Y-m-d')
            ]
        );

        $this->assertArrayHasKey($technician3, $result);
        $this->assertArrayNotHasKey($technician4, $result);

        $this->assertEquals(1,count($result[$technician3]));

        $this->assertEquals($paymentLabor31['actual_hours'], $result[$technician3][0]['actual_hours']);
        $this->assertEquals($paymentLabor31['paid_hours'], $result[$technician3][0]['paid_hours']);
        $this->assertEquals($paymentLabor31['billed_hours'], $result[$technician3][0]['billed_hours']);
        $this->assertEquals($paymentLabor31['quantity'] * $paymentLabor31['unit_price'], $result[$technician3][0]['labor_sale_amount']);
        $this->assertEquals($paymentLabor31['quantity'] * $paymentLabor31['dealer_cost'], $result[$technician3][0]['labor_cost_amount']);
    }

    public function serviceReportPaymentLaborProvider(): array
    {
        return [
            [
                [
                    'actual_hours' => 78.00,
                    'paid_hours' => 56.00,
                    'billed_hours' => 64.00,
                    'invoice_total' => 777.00,
                    'doc_num' => 'test31',
                    'sale_date' => (new \DateTime())->modify('-1 day'),
                    'sales_person_id' => 555,
                    'customer_name' => 'test_customer_name473',
                    'created_at' => (new \DateTime())->modify('-1 day'),
                    'quantity' => 2,
                    'unit_price' => 10,
                    'dealer_cost' => 5,
                ],
                [
                    'actual_hours' => 23.00,
                    'paid_hours' => 34.00,
                    'billed_hours' => 56.00,
                    'invoice_total' => 200.00,
                    'doc_num' => 'test32',
                    'sale_date' => (new \DateTime())->modify('-1 month'),
                    'sales_person_id' => 666,
                    'customer_name' => 'test_customer_name256',
                    'created_at' => (new \DateTime())->modify('-1 month'),
                    'quantity' => 3,
                    'unit_price' => 45,
                    'dealer_cost' => 35,
                ],
            ],
        ];
    }
}
