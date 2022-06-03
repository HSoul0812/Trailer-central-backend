<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Controllers\Bulk\Parts;

use App\Exceptions\Common\BusyJobException;
use App\Http\Controllers\v1\Bulk\Parts\BulkUploadController;
use App\Http\Controllers\v1\Showroom\ShowroomBulkUpdateController;
use App\Http\Requests\Bulk\Parts\CreateBulkUploadRequest;
use App\Http\Requests\Bulk\Parts\GetBulkUploadsRequest;
use App\Http\Requests\Showroom\ShowroomBulkUpdateVisibilityRequest;
use App\Http\Requests\Showroom\ShowroomBulkUpdateYearRequest;
use App\Jobs\ProcessBulkUpload;
use App\Jobs\Showroom\ShowroomBulkUpdateVisibility;
use App\Jobs\Showroom\ShowroomBulkUpdateYear;
use Dingo\Api\Exception\ResourceException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Ramsey\Uuid\Uuid;
use Exception;
use Tests\database\seeds\Showroom\ShowroomSeeder;
use Tests\Integration\IntegrationTestCase;

/**
 * @covers \App\Http\Controllers\v1\Bulk\Parts\BulkUploadController
 * @group MonitoredJobs
 */
class ShowroomBulkUpdateControllerTest extends IntegrationTestCase
{

    /**
     * @dataProvider invalidParametersForUpdateYearProvider
     *
     * @covers ::bulkUpdateYear
     *
     * @param array $params
     * @param string $expectedException
     * @param string $expectedExceptionMessage
     * @param string|null $firstExpectedErrorMessage
     *
     * @throws BusyJobException
     */
    public function testUpdateYearWithWrongParameters(array $params,
                                                  string $expectedException,
                                                  string $expectedExceptionMessage,
                                                  ?string $firstExpectedErrorMessage): void
    {
        // Given a Showroom
        $showroomSeeder = new ShowroomSeeder();
        $showroomSeeder->seed();

        // And I'm using the controller "ShowroomBulkUpdateController"
        $controller = app(ShowroomBulkUpdateController::class);

        // And I have a bad formed "ShowroomBulkUpdateYearRequest" request
        $request = new ShowroomBulkUpdateYearRequest($showroomSeeder->extractValues($params));

        // Then I expect to see a specific exception to be thrown
        $this->expectException($expectedException);
        // And I also expect to see a specific exception message
        $this->expectExceptionMessage($expectedExceptionMessage);

        try {
            // When I call the bulkUpdateYear action using the bad formed request
            $controller->bulkUpdateYear($request);
            $showroomSeeder->cleanUp();
        } catch (ResourceException $exception) {
            // Then I should see that the first error message has a specific string
            self::assertSame($firstExpectedErrorMessage, $exception->getErrors()->first());
            $showroomSeeder->cleanUp();

            throw $exception;
        }
    }

    /**
     * @dataProvider validParametersForUpdateYearProvider
     *
     * @covers ::bulkUpdateYear
     *
     * @param array $params
     *
     * @throws BusyJobException
     */
    public function testUpdateYearWithValidParameters(array $params): void
    {
        // Given a Showroom
        $showroomSeeder = new ShowroomSeeder();
        $showroomSeeder->seed();

        // And I'm using the controller "ShowroomBulkUpdateController"
        $controller = app(ShowroomBulkUpdateController::class);
        // And I have a well-formed "ShowroomBulkUpdateYearRequest" request
        $request = new ShowroomBulkUpdateYearRequest($showroomSeeder->extractValues($params));

        Bus::fake();

        // When I call the bulkUpdateYear action using the well-formed request
        $response = $controller->bulkUpdateYear($request);

        // Then I should see that job wit a specific name was enqueued
        Bus::assertDispatched(ShowroomBulkUpdateYear::class);
        // And I should see that response status is 200
        self::assertEquals(JsonResponse::HTTP_OK, $response->status());

        $showroomSeeder->cleanUp();
    }

    /**
     * @dataProvider invalidParametersForUpdateVisibilityProvider
     *
     * @covers ::bulkUpdateVisibility
     *
     * @param array $params
     * @param string $expectedException
     * @param string $expectedExceptionMessage
     * @param string|null $firstExpectedErrorMessage
     *
     * @throws BusyJobException
     */
    public function testUpdateVisibilityWithWrongParameters(array $params,
                                                      string $expectedException,
                                                      string $expectedExceptionMessage,
                                                      ?string $firstExpectedErrorMessage): void
    {
        // Given a Showroom
        $showroomSeeder = new ShowroomSeeder();
        $showroomSeeder->seed();

        // And I'm using the controller "ShowroomBulkUpdateController"
        $controller = app(ShowroomBulkUpdateController::class);

        // And I have a bad formed "ShowroomBulkUpdateVisibilityRequest" request
        $request = new ShowroomBulkUpdateVisibilityRequest($showroomSeeder->extractValues($params));

        // Then I expect to see a specific exception to be thrown
        $this->expectException($expectedException);
        // And I also expect to see a specific exception message
        $this->expectExceptionMessage($expectedExceptionMessage);

        try {
            // When I call the bulkUpdateVisibility action using the bad formed request
            $controller->bulkUpdateVisibility($request);
            $showroomSeeder->cleanUp();
        } catch (ResourceException $exception) {
            // Then I should see that the first error message has a specific string
            self::assertSame($firstExpectedErrorMessage, $exception->getErrors()->first());
            $showroomSeeder->cleanUp();

            throw $exception;
        }
    }

    /**
     * @dataProvider validParametersForUpdateVisibilityProvider
     *
     * @covers ::bulkUpdateVisibility
     *
     * @param array $params
     *
     * @throws BusyJobException
     */
    public function testUpdateVisibilityWithValidParameters(array $params): void
    {
        // Given a Showroom
        $showroomSeeder = new ShowroomSeeder();
        $showroomSeeder->seed();

        // And I'm using the controller "ShowroomBulkUpdateController"
        $controller = app(ShowroomBulkUpdateController::class);
        // And I have a well-formed "ShowroomBulkUpdateVisibilityRequest" request
        $request = new ShowroomBulkUpdateVisibilityRequest($showroomSeeder->extractValues($params));

        Bus::fake();

        // When I call the bulkUpdateVisibility action using the well-formed request
        $response = $controller->bulkUpdateVisibility($request);

        // Then I should see that job wit a specific name was enqueued
        Bus::assertDispatched(ShowroomBulkUpdateVisibility::class);
        // And I should see that response status is 200
        self::assertEquals(JsonResponse::HTTP_OK, $response->status());

        $showroomSeeder->cleanUp();
    }

    /**
     * Examples of invalid query parameter with their respective expected exception and its message
     *
     * @return array<string, array>
     */
    public function invalidParametersForUpdateYearProvider(): array
    {
        return [                                            // array $parameters, string $expectedException, string $expectedExceptionMessage, string $firstExpectedErrorMessage
            'No manufacturer'  => [[], ResourceException::class, 'Validation Failed', 'The manufacturer field is required.'],
            'No from year'     => [['manufacturer' => 'Testing Showroom', 'to' => '2022'], ResourceException::class, 'Validation Failed', 'The from field is required.'],
            'No to year'       => [['manufacturer' => 'Testing Showroom', 'from' => '2019'], ResourceException::class, 'Validation Failed', 'The to field is required.'],
        ];
    }

    /**
     * Examples of invalid query parameter with their respective expected exception and its message
     *
     * @return array<string, array>
     */
    public function validParametersForUpdateYearProvider(): array
    {
        return [
            'Valid Data'  => [[
                'manufacturer' => 'Testing Showroom',
                'from' => '2019',
                'to' => '2022'
            ]]
        ];
    }

    /**
     * Examples of invalid query parameter with their respective expected exception and its message
     *
     * @return array<string, array>
     */
    public function invalidParametersForUpdateVisibilityProvider(): array
    {
        return [               // array $parameters, string $expectedException, string $expectedExceptionMessage, string $firstExpectedErrorMessage
            'No manufacturer'  => [[], ResourceException::class, 'Validation Failed', 'The manufacturer field is required.'],
            'No from year'     => [['manufacturer' => 'Testing Showroom', 'visibility' => false], ResourceException::class, 'Validation Failed', 'The year field is required.'],
            'No visibility'    => [['manufacturer' => 'Testing Showroom', 'year' => '2019'], ResourceException::class, 'Validation Failed', 'The visibility field is required.'],
        ];
    }

    /**
     * Examples of invalid query parameter with their respective expected exception and its message
     *
     * @return array<string, array>
     */
    public function validParametersForUpdateVisibilityProvider(): array
    {
        return [
            'Valid Data'  => [[
                'manufacturer' => 'Testing Showroom',
                'year' => '2019',
                'visibility' => false
            ]]
        ];
    }
}
