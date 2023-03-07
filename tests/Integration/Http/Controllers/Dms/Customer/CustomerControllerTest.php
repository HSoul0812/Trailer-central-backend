<?php

namespace Tests\Integration\Http\Controllers\Dms\Customer;

use App\Http\Controllers\v1\Dms\Customer\CustomerController;
use App\Http\Requests\Dms\DeleteCustomerRequest;
use Dingo\Api\Exception\DeleteResourceFailedException;
use Dingo\Api\Exception\ResourceException;
use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Tests\database\seeds\Dms\Customer\CustomerSeeder;
use Tests\TestCase;


/**
 * Class CustomerControllerTest.
 *
 * @covers CustomerController
 */
class CustomerControllerTest extends TestCase
{
    /**
     * @var CustomerSeeder
     */
    private $seeder;


    /**
     * @covers ::destroy
     *
     * @group DMS
     * @group DMS_CUSTOMER
     *
     * @throws BindingResolutionException
     */
    public function testWithValidParams(): void
    {
        // Given I'm a customer
        $customerId = $this->seeder->customers[0][0]->getKey();
        // And a dealer

        $dealerId = $this->seeder->dealers[0]->getKey();
        // And I'm using the controller "Customer"

        $controller = app()->make(CustomerController::class);
        // Then I make the valid request

        $request = new DeleteCustomerRequest(['dealer_id' => $dealerId]);
        // When I call destroy action using the valid request

        $response = $controller->destroy($customerId, $request);

        // Then I should see the customer not exist on database
        $this->assertSoftDeleted('dms_customer', ['id' => $customerId, 'dealer_id' => $dealerId]);

        // And I should see that response status is 202
        self::assertEquals(JsonResponse::HTTP_NO_CONTENT, $response->status());
    }


    /**
     * @dataProvider invalidDataProviderForRequest
     *
     * @param array $params
     * @param string $expectedException
     * @param string $expectedExceptionMessage
     * @param string|null $firstExpectedErrorMessage
     *
     * @throws Exception
     * @covers ::destroy
     *
     * @group DMS
     * @group DMS_CUSTOMER
     */
    public function testWithInvalidParams(array $params,
                                          string $expectedException,
                                          string $expectedExceptionMessage,
                                          ?string $firstExpectedErrorMessage): void
    {

        // And I'm using the controller "Customer"
        $controller = app(CustomerController::class);

        // And I have a bad formed "DeleteCustomerRequest" request
        $_params = $this->seeder->extractValues($params);

        $request = new DeleteCustomerRequest($_params);

        // Then I expect to see an specific exception to be thrown
        $this->expectException($expectedException);

        // And I also expect to see an specific exception message
        $this->expectExceptionMessage($expectedExceptionMessage);

        try
        {
            // When I call the destroy action using request with bad params
            $controller->destroy((int)$request->getObjectIdValue(), $request);
        } catch (DeleteResourceFailedException $exception)
        {
            // Then I should see that the first error message has a specific string
            self::assertSame($firstExpectedErrorMessage, $exception->getErrors()->first());
            throw $exception;
        } catch (Exception $exception)
        {
            throw $exception;
        }
    }

    public function validCustomernameProvider()
    {
        return [
            [
                'first_name' => 'Juan Carlos',
                'middle_name' => 'Diego-Sanchez',
                'last_name' => 'Rodriguez Jr.'
            ],
            [
                'first_name' => 'Sara',
                'middle_name' => 'Mary Anne',
                'last_name' => 'Petersen-Nelson'
            ],
            [
                'first_name' => 'Dr. Blake',
                'middle_name' => 'Hunter',
                'last_name' => 'Nelson Sr.'
            ],
            [
                'first_name' => 'Anna-Molly',
                'middle_name' => 'LeAnne',
                'last_name' => 'Ray II'
            ]
        ];
    }

    /**
     * @dataProvider validCustomernameProvider
     * 
     * @group DMS
     * @group DMS_CUSTOMER
     */
    public function testCreateCustomerWithValidCustomerName($first_name, $middle_name, $last_name)
    {
        $params = [
            'fist_name' => $first_name,
            'middle_name' => $middle_name,
            'last_name' => $last_name,
            'display_name' => $first_name .' '. $middle_name .' '. $last_name,
            'default_discount_percent' => 0
        ];

        $token = $this->seeder->dealers[0]->authToken->access_token;

        $response = $this->json(
            'PUT',
            '/api/user/customers',
            $params,
            ['access-token' => $token]
        );

        $response->assertSuccessful()
            ->assertJsonMissingValidationErrors();
    }

    /**
     * Examples of invalid querystring params with their respective exceptions and messages
     *
     * @return array[]
     */
    public function invalidDataProviderForRequest(): array
    {
        return [                        // array $parameters, string $expectedException, string $expectedExceptionMessage, string $firstExpectedErrorMessage
            'Bad dealer type' => [['dealer_id' => 'aback'], ResourceException::class, 'Validation Failed', 'The dealer id needs to be an integer.'],
            'No dealer' => [[], ResourceException::class, 'Validation Failed', 'The dealer id field is required.'],
            'Non existent dealer' => [['dealer_id' => 23654789], ResourceException::class, 'Validation Failed', 'The selected dealer id is invalid.'],
            'Non existent Customer' => [['id' => 666666, 'dealer_id' => $this->getSeededData(0, 'dealer_id')], ModelNotFoundException::class, 'No query results for model [App\Models\CRM\User\Customer].', null],
        ];
    }

    /**
     * @param int $dealerIndex the array index of a dealer
     * @param string $keyName the key name of the needed value
     * @return callable
     */
    protected function getSeededData(int $dealerIndex, string $keyName): callable
    {
        /**
         * @param CustomerSeeder $seeder
         * @return mixed
         * @throws Exception
         */
        return static function (CustomerSeeder $seeder) use ($dealerIndex, $keyName)
        {
            switch ($keyName)
            {
                case 'dealer_id':
                    return $seeder->dealers[$dealerIndex]->getKey();
                case 'random_customer_id':
                    return $seeder->customers[$dealerIndex][random_int(0, 1)]->getKey();
            }

            return null;
        };
    }

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->seeder = $this->seeder ?? new CustomerSeeder();
        $this->seeder->seed();
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown(): void
    {
        $this->seeder->cleanUp();

        parent::tearDown();
    }
}
