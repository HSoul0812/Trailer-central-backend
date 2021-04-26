<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Controllers\Integration\CVR;

use App\Http\Controllers\v1\Integration\CvrController;
use App\Http\Requests\Integration\CVR\SendFileRequest;
use App\Jobs\Integration\CVR\CvrSendFileJob;
use Dingo\Api\Exception\ResourceException;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Ramsey\Uuid\Uuid;
use Tests\Integration\AbstractMonitoredJobsTest;
use Tests\database\seeds\Dms\CVRSeeder;

/**
 * @covers \App\Http\Controllers\v1\Integration\CvrController
 * @group MonitoredJobs
 */
class CvrControllerTest extends AbstractMonitoredJobsTest
{
    public function setUp(): void
    {
        parent::setUp();
        $this->seeder = new CVRSeeder();
    }
    
    /**
     * @dataProvider invalidParametersForCreationProvider
     *
     * @covers ::create
     *
     * @param array $params
     * @param string $expectedException
     * @param string $expectedExceptionMessage
     * @param string|null $firstExpectedErrorMessage
     */
    public function testCreateWithWrongParameters(array $params,
                                                  string $expectedException,
                                                  string $expectedExceptionMessage,
                                                  ?string $firstExpectedErrorMessage): void
    {
        $this->seeder->seed();

        // And I'm using the controller "CvrController"
        $controller = app(CvrController::class);

        // And I have a bad formed "SendFileRequest" request
        $request = new SendFileRequest($this->seeder->extractValues($params));

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
     * @param array $params
     */
    public function testCreateWithValidParameters(array $params): void
    {
        $this->seeder->seed();
        $controllerParams = array_merge($this->seeder->extractValues($params), ['unit_sale_id' => $this->seeder->unitSale->id]);

        // And I'm using the controller "CvrController"
        $controller = app(CvrController::class);
        // And I have a well formed "SendFileRequest" request
        $request = new SendFileRequest($controllerParams);

        Bus::fake();

        // When I call the create action using the well formed request
        $response = $controller->create($request);

        // Then I should see that job wit a specific name was enqueued
        Bus::assertDispatched(CvrSendFileJob::class);
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
        $fileUploaded = UploadedFile::fake()->create('some-filename.zip', 7800);

        return [              // array $parameters, string $expectedException, string $expectedExceptionMessage, string $firstExpectedErrorMessage
            'No dealer'       => [[], ResourceException::class, 'Validation Failed', 'The dealer id field is required.'],
            'Bad token'       => [['dealer_id' => 666999, 'token' => 'this-is-a-token', 'document' => $fileUploaded,], ResourceException::class, 'Validation Failed', 'The token must be a valid UUID.']
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
        $fileUploaded = UploadedFile::fake()->create('some-filename.zip', 7800);

        return [           // array $parameters
            'No token'   => [['dealer_id' => $this->getSeededData(0,'id'), 'document' => $fileUploaded]],
            'With token' => [['dealer_id' => $this->getSeededData(1,'id'), 'document' => $fileUploaded, 'token' => Uuid::uuid4()->toString()]],
        ];
    }
}
