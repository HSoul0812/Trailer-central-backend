<?php

declare(strict_types=1);

namespace Tests\Integration\Repositories\Inventory\Floorplan;

use App\Models\Inventory\Floorplan\Payment;
use App\Models\Inventory\Inventory;
use App\Models\User\User;
use App\Models\CRM\Dms\Quickbooks\Account;
use App\Repositories\Inventory\Floorplan\PaymentRepository;
use App\Repositories\Inventory\Floorplan\PaymentRepositoryInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Faker\Factory as Faker;
use Faker\Provider\Uuid;
use Tests\TestCase;

class PaymentRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test that SUT is properly bound by the application
     *
     * @group DMS
     * @group DMS_INVENTORY_FLOORPLAN
     *
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     * @note IntegrationTestCase
     */
    public function testIoCForTheRepositoryInterfaceIsWorking(): void
    {
        $concreteRepository = $this->getConcreteRepository();

        self::assertInstanceOf(PaymentRepository::class, $concreteRepository);
    }

    /**
     * @group DMS
     * @group DMS_INVENTORY_FLOORPLAN
     *
     * @throws BindingResolutionException when there is a problem with resolution of concreted class
     * @note IntegrationTestCase
     */
    public function testNumberOfBulkPaymentsCreatedAsExpected(): void {
        $paymentsData = $this->generatePaymentsData();
        $payments = $this->getConcreteRepository()->createBulk($paymentsData['payments']);

        self::assertSame(count($paymentsData['payments']), count($payments));
    }

    /**
     * @group DMS
     * @group DMS_INVENTORY_FLOORPLAN
     *
     * @throws BindingResolutionException when there is a problem with resolution of concreted class
     * @note IntegrationTestCase
     */
    public function testCreateFloorplanPaymentAsExpected(): void {
        $paymentsData = $this->generatePaymentsData(1);
        $payment = $this->getConcreteRepository()->create($paymentsData['payments'][0]);

        self::assertInstanceOf(Payment::class, $payment);
    }

    /**
     * @return PaymentRepositoryInterface
     *
     * @throws BindingResolutionException when there is a problem with resolution
     *                                    of concreted class
     *
     */
    protected function getConcreteRepository(): PaymentRepositoryInterface
    {
        return $this->app->make(PaymentRepositoryInterface::class);
    }

    private function generatePaymentsData($maxOfPayments = 5)
    {
        $paymentsData = [];
        $faker = Faker::create();
        $countOfPayments = $faker->numberBetween(1, $maxOfPayments);
        $dealerId = factory(User::class)->create()->getKey();

        foreach (range(1, $countOfPayments) as $paymentIndex) {
            $inventoryId = factory(Inventory::class)->create(['dealer_id' => $dealerId])->getKey();
            $accountId = factory(Account::class)->create(['dealer_id' => $dealerId])->getKey();

            $paymentsData[] = [
                'inventory_id' => $inventoryId,
                'type' => $faker->randomElement(Payment::PAYMENT_CATEGORIES),
                'payment_type' => $faker->randomElement(Payment::PAYMENT_TYPES),
                'account_id' => $accountId,
                'amount' => $faker->randomFloat(2, 100, 500)
            ];
        }

        return ['payments' => $paymentsData, 'dealerId' => $dealerId];
    }
}
