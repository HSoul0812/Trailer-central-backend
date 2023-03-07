<?php

namespace Tests\Unit\Domains\QuickBooks\Actions;

use App\Domains\QuickBooks\Actions\DeleteBillInQuickBooksAction;
use App\Domains\QuickBooks\Actions\SetupQuickBooksSDKForDealerAction;
use App\Models\CRM\Dms\Quickbooks\Bill;
use App\Models\User\User;
use Exception;
use Faker\Factory;
use Mockery;
use TCentral\QboSdk\Domain\Bill\Actions\DeleteBillByIdAction;
use Tests\TestCase;

class DeleteBillInQuickBooksActionTest extends TestCase
{
    /**
     * @group DMS
     * @group DMS_BILLS
     *
     * @throws Exception
     */
    public function testItCanDeleteBillInQuickBooks()
    {
        $faker = Factory::create();

        $dealer = factory(User::class)->create();
        $bill = factory(Bill::class)->create([
            'dealer_id' => $dealer->dealer_id,
            'qb_id' => $faker->numberBetween(1, 10000),
        ]);

        $setupQuickBooksSDKForDealerAction = Mockery::mock(SetupQuickBooksSDKForDealerAction::class);
        $deleteBillByIdAction = Mockery::mock(DeleteBillByIdAction::class);

        $setupQuickBooksSDKForDealerAction
            ->expects('execute')
            ->withArgs(function(User $arg) use ($dealer) {
                return $arg->dealer_id === $dealer->dealer_id;
            })
            ->once()
            ->andReturns();

        $deleteBillByIdAction
            ->expects('execute')
            ->with($bill->qb_id)
            ->once()
            ->andReturns();

        $action = new DeleteBillInQuickBooksAction(
            $setupQuickBooksSDKForDealerAction,
            $deleteBillByIdAction
        );

        $action->execute($bill);

        $this->assertNull($bill->qb_id);

        $dealer->delete();
        $bill->delete();
    }
}
