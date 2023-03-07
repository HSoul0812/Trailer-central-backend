<?php

namespace Tests\Unit\Domains\UnitSale\Actions;

use App\Domains\UnitSale\Actions\ExportUnitSalesSummaryCsvAction;
use App\Exceptions\EmptyPropValueException;
use App\Exceptions\File\FileUploadException;
use App\Models\User\User;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Str;
use League\Csv\CannotInsertRecord;
use League\Csv\Exception;
use Storage;
use Tests\TestCase;
use Throwable;

class ExportUnitSalesSummaryCsvActionTest extends TestCase
{
    /**
     * @group DMS
     * @group DMS_REPORTS
     *
     * @throws CannotInsertRecord
     * @throws EmptyPropValueException
     * @throws Exception
     * @throws FileNotFoundException
     * @throws FileUploadException
     * @throws Throwable
     */
    public function testItCanExportDataToS3()
    {
        $dealer = factory(User::class)->create();
        $fileName = Str::random() . '.csv';

        /** @var ExportUnitSalesSummaryCsvAction $action */
        $action = resolve(ExportUnitSalesSummaryCsvAction::class)
            ->fromDealer($dealer)
            ->from(now()->subMonth()->startOfDay())
            ->to(now()->endOfDay())
            ->withFilename($fileName);

        Storage::fake('s3');
        Storage::fake('tmp');

        $action->execute();

        Storage::disk('tmp')->assertExists($fileName);
    }

    /**
     * @group DMS
     * @group DMS_REPORTS
     *
     * @throws CannotInsertRecord
     * @throws EmptyPropValueException
     * @throws Exception
     * @throws FileNotFoundException
     * @throws FileUploadException
     * @throws Throwable
     */
    public function testItThrowsExceptionIfPropsAreInvalid()
    {
        $this->expectException(EmptyPropValueException::class);

        /** @var ExportUnitSalesSummaryCsvAction $action */
        $action = resolve(ExportUnitSalesSummaryCsvAction::class);

        $action->execute();
    }

    /**
     * @group DMS
     * @group DMS_REPORTS
     *
     * @return void
     */
    public function testTheMergeHeadersMethodWorksProperly()
    {
        $invoiceNo = Str::random(8);
        $invoiceDate = Str::random(8);

        $headers = resolve(ExportUnitSalesSummaryCsvAction::class)
            ->mergeHeaders([
                'new_key' => Str::random(8),
                'new_key_2' => Str::random(8),
                'invoice_no' => $invoiceNo,
                'invoice_date' => $invoiceDate,
            ])
            ->getHeaders();

        // The mergeHeaders method should remove these keys
        // and not merge them to the actual headers
        $this->assertArrayNotHasKey('new_key', $headers);
        $this->assertArrayNotHasKey('new_key_2', $headers);

        // The actual headers value should be updated properly
        $this->assertEquals($invoiceNo, $headers['invoice_no']);
        $this->assertEquals($invoiceDate, $headers['invoice_date']);
    }
}
