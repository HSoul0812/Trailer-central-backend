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
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Class UnitSaleLaborRepositoryTest
 * @package Tests\Integration\Repositories\Dms
 *
 * @coversDefaultClass \App\Repositories\Dms\UnitSaleLaborRepository
 */
class PaymentLaborRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @covers ::serviceReport
     * @dataProvider serviceReportProvider
     *
     * @param array $paymentLabor31
     * @param array $paymentLabor32
     */
    public function testServiceReport(
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
                'unit_sale_id' => null,
                'dealer_id' => self::getTestDealerId(),
                'total' => $paymentLabor31['invoice_total'],
                'doc_num' => $paymentLabor31['doc_num'],
                'invoice_date' => $paymentLabor31['sale_date'],
                'customer_id' => $customerId31,
            ]
        )->id;

        $invoiceId32 = factory(Invoice::class)->create(
            [
                'unit_sale_id' => null,
                'dealer_id' => self::getTestDealerId(),
                'total' => $paymentLabor32['invoice_total'],
                'doc_num' => $paymentLabor32['doc_num'],
                'invoice_date' => $paymentLabor32['sale_date'],
                'customer_id' => $customerId32,
            ]
        )->id;

        $paymentId31 = factory(Payment::class)->create(
            [
                'invoice_id' => $invoiceId31,
                'dealer_id' => self::getTestDealerId(),
                'created_at' => $paymentLabor31['created_at'],
            ]
        );

        $paymentId32 = factory(Payment::class)->create(
            [
                'invoice_id' => $invoiceId32,
                'dealer_id' => self::getTestDealerId(),
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
        $result = $repository->serviceReport(['dealer_id' => $this->getTestDealerId()]);

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
     * @dataProvider serviceReportProvider
     *
     * @param array $paymentLabor31
     * @param array $paymentLabor32
     */
    public function testServiceReportwithDate(
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
                'unit_sale_id' => null,
                'dealer_id' => self::getTestDealerId(),
                'total' => $paymentLabor31['invoice_total'],
                'doc_num' => $paymentLabor31['doc_num'],
                'invoice_date' => $paymentLabor31['sale_date'],
                'customer_id' => $customerId31,
            ]
        )->id;

        $invoiceId32 = factory(Invoice::class)->create(
            [
                'unit_sale_id' => null,
                'dealer_id' => self::getTestDealerId(),
                'total' => $paymentLabor32['invoice_total'],
                'doc_num' => $paymentLabor32['doc_num'],
                'invoice_date' => $paymentLabor32['sale_date'],
                'customer_id' => $customerId32,
            ]
        )->id;

        $paymentId31 = factory(Payment::class)->create(
            [
                'invoice_id' => $invoiceId31,
                'dealer_id' => self::getTestDealerId(),
                'created_at' => $paymentLabor31['created_at'],
            ]
        );

        $paymentId32 = factory(Payment::class)->create(
            [
                'invoice_id' => $invoiceId32,
                'dealer_id' => self::getTestDealerId(),
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
                'dealer_id' => $this->getTestDealerId(),
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

    public function serviceReportProvider(): array
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
