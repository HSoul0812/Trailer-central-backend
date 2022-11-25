<?php

namespace Tests\Unit\Domains\UnitSale\Actions;

use App\Domains\UnitSale\Actions\ExportUnitSalesSummaryCsvAction;
use App\Exceptions\EmptyPropValueException;
use App\Models\User\User;
use League\Csv\CannotInsertRecord;
use League\Csv\Exception;
use Storage;
use Tests\TestCase;

class ExportUnitSalesSummaryCsvActionTest extends TestCase
{
    /**
     * @throws CannotInsertRecord
     * @throws Exception
     * @throws EmptyPropValueException
     */
    public function testItCanExportDataToS3()
    {
        $dealer = factory(User::class)->create();

        /** @var ExportUnitSalesSummaryCsvAction $action */
        $action = resolve(ExportUnitSalesSummaryCsvAction::class)
            ->fromDealer($dealer)
            ->from(now()->subMonth()->startOfDay())
            ->to(now()->endOfDay());

        Storage::fake('s3');
        Storage::fake('tmp');

        $fullFilePath = $action->execute();
    }

    /**
     * @throws CannotInsertRecord
     * @throws Exception
     */
    public function testItThrowsExceptionIfPropsAreInvalid()
    {
        $this->expectException(EmptyPropValueException::class);

        /** @var ExportUnitSalesSummaryCsvAction $action */
        $action = resolve(ExportUnitSalesSummaryCsvAction::class);

        $action->execute(uniqid() . '.csv');
    }
}
