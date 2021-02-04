<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Contollers\Bulk\Parts;

use App\Exceptions\Common\BusyJobException;
use App\Http\Controllers\v1\Bulk\Parts\BulkDownloadController;
use App\Http\Requests\Bulk\Parts\CreateBulkDownloadRequest;
use App\Jobs\Bulk\Parts\CsvExportJob;
use App\Models\Bulk\Parts\BulkDownload;
use App\Models\Common\MonitoredJob;
use Dingo\Api\Exception\ResourceException;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Bus;
use Ramsey\Uuid\Uuid;
use Tests\Integration\AbstractMonitoredJobsTest;

/**
 * @covers \App\Http\Controllers\v1\Bulk\Parts\BulkDownloadController
 */
class BulkDownloadControllerTest extends AbstractMonitoredJobsTest
{
    /**
     * @dataProvider invalidParametersProvider
     *
     * @param array $params
     * @param string $expectedException
     * @param string $expectedExceptionMessage
     * @param string|null $firstExpectedErrorMessage
     *
     * @throws BusyJobException
     */
    public function testCreateWithWrongParameters(array $params,
                                                  string $expectedException,
                                                  string $expectedExceptionMessage,
                                                  ?string $firstExpectedErrorMessage): void
    {
        // Given I have few dealers
        $this->seeder->seedDealers();

        // And I'm using the controller "BulkDownloadController"
        $controller = app(BulkDownloadController::class);

        $paramsExtracted = $this->seeder->extractValues($params);

        if ($expectedException === BusyJobException::class) {
            // And I have a monitored job "parts-export-new" which is currently running
            factory(MonitoredJob::class)->create([
                'dealer_id' => $paramsExtracted['dealer_id'],
                'name' => BulkDownload::QUEUE_JOB_NAME,
                'concurrency_level' => BulkDownload::LEVEL_DEFAULT,
                'queue' => BulkDownload::QUEUE_NAME,
                'status' => BulkDownload::STATUS_PROCESSING
            ]);
        }

        // And I have a bad formed "CreateBulkDownloadRequest" request
        $request = new CreateBulkDownloadRequest($paramsExtracted);

        // Then I expect to see an specific exception to be thrown
        $this->expectException($expectedException);
        // And I also expect to see an specific exception message
        $this->expectExceptionMessage($expectedExceptionMessage);

        try {
            // When I call the create action using the bad formed request
            $controller->create($request);
        } catch (ResourceException $exception) {
            // Then I should see that the first error message has a specific string
            self::assertSame($firstExpectedErrorMessage, $exception->getErrors()->first());

            throw $exception;
        }
    }

    /**
     * @dataProvider validParametersProvider
     *
     * @param array $params
     *
     * @throws BusyJobException
     */
    public function testCreateWithValidParameters(array $params): void
    {
        // Given I have few dealers
        $this->seeder->seedDealers();

        // And I'm using the controller "BulkDownloadController"
        $controller = app(BulkDownloadController::class);
        // And I have a well formed "CreateBulkDownloadRequest" request
        $request = new CreateBulkDownloadRequest($this->seeder->extractValues($params));

        Bus::fake();

        // When I call the create action using the well formed request
        $response = $controller->create($request);

        // Then I should see that job wit a specific name was enqueued
        Bus::assertDispatched(CsvExportJob::class);
        // And I should see that response status is 202
        self::assertEquals(JsonResponse::HTTP_ACCEPTED, $response->status());
    }

    /**
     * Examples of invalid query parameter with their respective expected exception and its message
     *
     * @return array<string, array>
     * @throws Exception when Uuid::uuid4 cannot generate a uuid
     */
    public function invalidParametersProvider(): array
    {
        return [                                            // array $parameters, string $expectedException, string $expectedExceptionMessage, string $firstExpectedErrorMessage
            'No dealer'                                     => [[], ResourceException::class, 'Validation Failed', 'The dealer id field is required.'],
            'Bad token'                                     => [['dealer_id' => 666999, 'token' => 'this-is-a-token'], ResourceException::class, 'Validation Failed', 'The token must be a valid UUID.'],
            'There is another job working'                  => [['dealer_id' => $this->getSeededData(0,'id')], BusyJobException::class, "This job can't be set up due there is currently other job working", null],
            'There is another job working (token provided)' => [['dealer_id' => $this->getSeededData(0,'id'),'token' => Uuid::uuid4()->toString()], BusyJobException::class, "This job can't be set up due there is currently other job working", null]
        ];
    }

    /**
     * Examples of invalid query parameter with their respective expected exception and its message
     *
     * @return array<string, array>
     * @throws Exception when Uuid::uuid4 cannot generate a uuid
     */
    public function validParametersProvider(): array
    {
        return [           // array $parameters
            'No token'   => [['dealer_id' => $this->getSeededData(0,'id')]],
            'With token' => [['dealer_id' => $this->getSeededData(1,'id'), 'token' => Uuid::uuid4()->toString()]]
        ];
    }
}
