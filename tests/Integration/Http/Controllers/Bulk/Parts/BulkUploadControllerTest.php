<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Controllers\Bulk\Parts;

use App\Exceptions\Common\BusyJobException;
use App\Http\Controllers\v1\Bulk\Parts\BulkUploadController;
use App\Http\Requests\Bulk\Parts\CreateBulkUploadRequest;
use App\Http\Requests\Bulk\Parts\GetBulkUploadsRequest;
use App\Jobs\ProcessBulkUpload;
use Dingo\Api\Exception\ResourceException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Ramsey\Uuid\Uuid;
use Tests\Integration\AbstractMonitoredJobsTest;
use Exception;

/**
 * @covers \App\Http\Controllers\v1\Bulk\Parts\BulkUploadController
 * @group MonitoredJobs
 */
class BulkUploadControllerTest extends AbstractMonitoredJobsTest
{
    /**
     * @covers ::index
     *
     * @group DMS
     * @group DMS_BULK
     * @group DMS_BULK_UPLOAD
     */
    public function testIndexListJobsWithBadParameters(): void
    {
        // Given I'm using the controller "BulkUploadController"
        $controller = app(BulkUploadController::class);
        // And I have a bad formed "GetBulkUploadsRequest"
        $request = new GetBulkUploadsRequest([]);

        // Then I expect to see an specific exception to be thrown
        $this->expectException(ResourceException::class);
        // And I also expect to see an specific exception message
        $this->expectExceptionMessage('Validation Failed');

        try {
            // When I call the index action using the bad formed request
            $controller->index($request);
        } catch (ResourceException $exception) {
            // Then I should see that the first error message has a specific string
            self::assertSame('The dealer id field is required.', $exception->getErrors()->first());

            throw $exception;
        }
    }

    /**
     * @dataProvider invalidParametersForCreationProvider
     *
     * @covers ::create
     *
     * @group DMS
     * @group DMS_BULK
     * @group DMS_BULK_UPLOAD
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

        // And I'm using the controller "BulkUploadController"
        $controller = app(BulkUploadController::class);

        // And I have a bad formed "CreateBulkUploadRequest" request
        $request = new CreateBulkUploadRequest($this->seeder->extractValues($params));

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
     * @dataProvider validParametersForCreationProvider
     *
     * @covers ::create
     *
     * @group DMS
     * @group DMS_BULK
     * @group DMS_BULK_UPLOAD
     *
     * @param array $params
     *
     * @throws BusyJobException
     */
    public function testCreateWithValidParameters(array $params): void
    {
        // Given I have few dealers
        $this->seeder->seedDealers();

        // And I'm using the controller "BulkUploadController"
        $controller = app(BulkUploadController::class);
        // And I have a well formed "CreateBulkUploadRequest" request
        $request = new CreateBulkUploadRequest($this->seeder->extractValues($params));

        Bus::fake();

        // When I call the create action using the well formed request
        $response = $controller->create($request);

        // Then I should see that job wit a specific name was enqueued
        Bus::assertDispatched(ProcessBulkUpload::class);
        // And I should see that response status is 200
        self::assertEquals(JsonResponse::HTTP_OK, $response->status());
    }

    /**
     * Examples of invalid query parameter with their respective expected exception and its message
     *
     * @return array<string, array>
     * @throws Exception when Uuid::uuid4 cannot generate a uuid
     */
    public function invalidParametersForCreationProvider(): array
    {
        $fileUploaded = UploadedFile::fake()->create('some-filename.csv', 7800);

        return [                                            // array $parameters, string $expectedException, string $expectedExceptionMessage, string $firstExpectedErrorMessage
            'No dealer'                                     => [[], ResourceException::class, 'Validation Failed', 'The dealer id field is required.'],
            'No csv file'                                   => [['dealer_id' => 666999], ResourceException::class, 'Validation Failed', 'The csv file field is required.'],
            'Bad token'                                     => [['dealer_id' => 666999, 'csv_file' => $fileUploaded, 'token' => 'this-is-a-token'], ResourceException::class, 'Validation Failed', 'The token must be a valid UUID.']
        ];
    }

    /**
     * Examples of invalid query parameter with their respective expected exception and its message
     *
     * @return array<string, array>
     * @throws Exception when Uuid::uuid4 cannot generate a uuid
     */
    public function validParametersForCreationProvider(): array
    {
        $fileUploaded = UploadedFile::fake()->create('some-filename.csv', 7800);

        return [           // array $parameters
            'No token'   => [['dealer_id' => $this->getSeededData(0,'id'), 'csv_file' => $fileUploaded]],
            'With token' => [['dealer_id' => $this->getSeededData(1,'id'), 'csv_file' => $fileUploaded, 'token' => Uuid::uuid4()->toString()]],
        ];
    }
}
