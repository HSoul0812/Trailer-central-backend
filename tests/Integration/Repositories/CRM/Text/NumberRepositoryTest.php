<?php

declare(strict_types=1);

namespace Tests\Integration\Repositories\CRM\Text;

use App\Models\CRM\Text\Number;
use App\Models\CRM\Text\NumberTwilio;
use App\Repositories\CRM\Text\NumberRepository;
use App\Repositories\CRM\Text\NumberRepositoryInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\database\seeds\CRM\Text\NumberSeeder;
use Tests\Integration\WithMySqlConstraintViolationsParser;
use Tests\TestCase;

class NumberRepositoryTest extends TestCase
{
    use WithMySqlConstraintViolationsParser;
    use WithFaker;

    /**
     * @var NumberSeeder
     */
    private $seeder;

    /**
     * @var Number
     */
    private $model = Number::class;

    /**
     * @var NumberRepository
     */
    private $repository = NumberRepository::class;

    private const DEALER_NUMBER = '+17863391202';
    private const CUSTOMER_NUMBER = '+18604402725';
    private const CUSTOMER_NAME = 'Awesome Customer Test';
    private const NEW_CUSTOMER_NAME = 'Another Awesome Customer Test';
    private $twilioNumber;
    private $createTwilioNumber;
    private $expirationTime;

    /**
     * Create the necessary data for the test
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->expirationTime = time() + ($this->model::EXPIRATION_TIME * 60 * 60);

        // If we don't specifically limit to 12 characters, it will cause issues with the tests
        // Because the database will limit the length, but won't alert the model
        $this->twilioNumber = substr($this->faker->e164PhoneNumber, 0, 12);
        $this->createTwilioNumber = substr($this->faker->e164PhoneNumber, 0, 12);

        $this->seeder = new NumberSeeder();
        $this->seeder->seed();
    }

    /**
     * Clean up the database after the test is done
     *
     * @return void
     */
    public function tearDown(): void
    {
        $this->seeder->cleanUp();

        parent::tearDown();
    }

    /**
     * Test that SUT is properly bound by the application
     *
     * @group CRM
     * @typeOfTest IntegrationTestCase
     *
     * @return void
     * @throws BindingResolutionException when there is a problem with the resolution of the concrete class
     */
    public function testRepositoryInterfaceIsBound(): void
    {
        $concreteRepository = $this->getConcreteRepository();
        self::assertInstanceOf($this->repository, $concreteRepository);
    }

    /**
     * Test that SUT can create a record correctly
     *
     * @group CRM
     * @typeOfTest IntegrationTestCase
     *
     * @throws BindingResolutionException
     *
     * @covers \App\Repositories\CRM\Text\NumberRepository::create
     */
    public function testCreate(): void
    {
        // When I have the right parameters
        $rawParameters = [
            'dealer_number' => self::DEALER_NUMBER,
            'customer_number' => self::CUSTOMER_NUMBER,
            'customer_name' => self::CUSTOMER_NAME,
            'twilio_number' => $this->twilioNumber,
            'expiration_time' => $this->expirationTime,
            'dealer_id' => $this->seeder->dealer->getKey()
        ];

        // Given that I call the create method on the repository
        $number = $this->getConcreteRepository()->create($rawParameters);

        // Then I should get a class which is an instance of Number
        $this->assertInstanceOf($this->model, $number);

        // Assert the record was created correctly
        $this->assertDatabaseHas($this->model::getTableName(), $number->toArray());
    }

    /**
     * Test that SUT can delete a record correctly
     *
     * @group CRM
     * @typeOfTest IntegrationTestCase
     *
     * @throws BindingResolutionException
     *
     * @covers \App\Repositories\CRM\Text\NumberRepository::delete
     */
    public function testDelete(): void
    {
        // Given that I have a Number
        $number = $this->getFirstNumber();

        // When I call delete on the repository
        $response = $this->getConcreteRepository()->delete([
            $number->getKeyName() => $number->getKey()
        ]);

        // I should get true, because the record was deleted
        $this->assertTrue($response);

        // And the record is no longer in the database
        $this->assertDatabaseMissing($this->model::TABLE_NAME, $number->toArray());
    }

    /**
     * Test that SUT is performing all desired operations (sort and filter) except pagination
     *
     * @group CRM
     * @typeOfTest IntegrationTestCase
     *
     * @throws BindingResolutionException
     *
     * @covers \App\Repositories\CRM\Text\NumberRepository::get
     */
    public function testGet(): void
    {
        // Given that I have a collection of Numbers, Get the first number
        $firstNumber = $this->getFirstNumber();

        // When I call get
        // Then I get a single Number
        $retrievedNumber = $this->getConcreteRepository()->get(['id' => $firstNumber->getKey()]);

        // Get must be a Number
        self::assertInstanceOf($this->model, $firstNumber);

        // Number id matches param id
        self::assertSame($retrievedNumber->getKey(), $firstNumber->getKey());
    }

    /**
     * Test that SUT throws an exception when called with invalid parameters
     *
     * @group CRM
     * @typeOfTest IntegrationTestCase
     *
     * @covers \App\Repositories\CRM\Text\NumberRepository::get
     * @throws BindingResolutionException
     */
    public function testGetWithException(): void
    {
        // When I call get with invalid parameters
        // Then I expect to see that an exception has been thrown with a specific message
        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage('No query results for model [App\Models\CRM\Text\Number].');

        // When I call get with invalid parameters
        $number = $this->getConcreteRepository()->get(['id' => null]);

        // Then I should NOT have a number
        self::assertNull($number);
    }

    /**
     * Test that SUT is performing all desired operations (sort and filter) except pagination
     *
     * @group CRM
     * @typeOfTest IntegrationTestCase
     *
     * @throws BindingResolutionException
     *
     * @covers \App\Repositories\CRM\Text\NumberRepository::get
     */
    public function testGetAll(): void
    {
        // Given that I have a collection of Numbers
        $numbers = $this->seeder->createdNumbers;

        // When I call getAll
        // Then I get all numbers
        $retrievedNumbers = $this->getConcreteRepository()->getAll([$this->seeder->dealer->getKeyName() => $this->seeder->dealer->getKey()]);

        // Get must be a paginated result
        self::assertInstanceOf(LengthAwarePaginator::class, $retrievedNumbers);

        // Number id matches param id
        self::assertSameSize($numbers, $retrievedNumbers);
    }

    /**
     * Test that SUT can update a record correctly
     *
     * @group CRM
     * @typeOfTest IntegrationTestCase
     *
     * @throws BindingResolutionException
     *
     * @covers \App\Repositories\CRM\Text\NumberRepository::update
     */
    public function testUpdate(): void
    {
        // Given that I have a Number
        $number = $this->getFirstNumber();

        // And I have params to update
        $params = $number->toArray();
        $params['customer_name'] = self::NEW_CUSTOMER_NAME;

        // When I call the update method on the repository
        $response = $this->getConcreteRepository()->update($params);

        // I get true because the record was updated correctly
        $this->assertTrue($response);

        // Assert the record was updated on the database
        $this->assertDatabaseHas($this->model::TABLE_NAME, [
            'id' => $number->id,
            'customer_name' => self::NEW_CUSTOMER_NAME
        ]);
    }

    /**
     * Test that SUT can set Phone as used, coming from the customer
     *
     * @group CRM
     * @typeOfTest IntegrationTestCase
     *
     * @throws BindingResolutionException
     *
     * @covers \App\Repositories\CRM\Text\NumberRepository::setPhoneAsUsed
     */
    public function testSetPhoneAsUsedAsCustomer()
    {
        // Given that I have the right parameters $fromNumber, $twilioNumber, $toNumber, $customerName, $dealerId
        $fromNumber = self::CUSTOMER_NUMBER;
        $twilioNumber = $this->twilioNumber;
        $toNumber = $this->seeder->dealerNumber;
        $customerName = self::CUSTOMER_NAME;
        $dealerId = $this->seeder->dealer->getKey();

        // And call the repository to set a phone as used
        $number = $this->getConcreteRepository()->setPhoneAsUsed(
            $fromNumber,
            $twilioNumber,
            $toNumber,
            $customerName,
            $dealerId
        );

        // I should get the right instance
        $this->assertInstanceOf(Number::class, $number);

        // And the record should be present in the database
        $this->assertDatabaseHas(Number::TABLE_NAME, [
            'dealer_number' => $toNumber,
            'twilio_number' => $twilioNumber,
            'customer_number' => $fromNumber,
            'customer_name' => $customerName,
            'dealer_id' => $dealerId,
        ]);
    }

    /**
     * Test that SUT can set Phone as used, coming from the dealer
     *
     * @group CRM
     * @typeOfTest IntegrationTestCase
     *
     * @throws BindingResolutionException
     *
     * @covers \App\Repositories\CRM\Text\NumberRepository::setPhoneAsUsed
     */
    public function testSetPhoneAsUsedAsDealer()
    {
        // Given that I have the right parameters $fromNumber, $twilioNumber, $toNumber, $customerName, $dealerId
        $fromNumber = $this->seeder->dealerNumber;
        $twilioNumber = $this->twilioNumber;
        $toNumber = self::CUSTOMER_NUMBER;
        $customerName = self::CUSTOMER_NAME;
        $dealerId = $this->seeder->dealer->getKey();

        // And call the repository to set a phone as used
        $number = $this->getConcreteRepository()->setPhoneAsUsed(
            $fromNumber,
            $twilioNumber,
            $toNumber,
            $customerName,
            $dealerId
        );

        // I should get the right instance
        $this->assertInstanceOf(Number::class, $number);

        // And the record should be present in the database
        $this->assertDatabaseHas(Number::TABLE_NAME, [
            'dealer_number' => $fromNumber,
            'twilio_number' => $twilioNumber,
            'customer_number' => $toNumber,
            'customer_name' => $customerName,
            'dealer_id' => $dealerId,
        ]);
    }

    /**
     * Test that SUT can check if a Number already exists
     *
     * @group CRM
     * @typeOfTest IntegrationTestCase
     *
     * @throws BindingResolutionException
     *
     * @covers \App\Repositories\CRM\Text\NumberRepository::existsTwilioNumber
     */
    public function testExistsTwilioNumber()
    {
        // Given that I have a twilio number
        $twilioNumber = $this->getFirstTwilioNumber();

        // And I call the repository to check if it exists
        $exists = $this->getConcreteRepository()->existsTwilioNumber($twilioNumber->phone_number);

        // I should get true because it does
        $this->assertTrue($exists);
    }

    /**
     * Test that SUT can create a NumberTwilio
     *
     * @group CRM
     * @typeOfTest IntegrationTestCase
     *
     * @throws BindingResolutionException
     *
     * @covers \App\Repositories\CRM\Text\NumberRepository::createTwilioNumber
     */
    public function testCreateTwilioNumber()
    {
        // Given that I have a phone
        $phone_number = $this->createTwilioNumber;

        // And I call the repository to create said number
        $twilioNumber = $this->getConcreteRepository()->createTwilioNumber($phone_number);

        // I should get the right instance
        $this->assertInstanceOf(NumberTwilio::class, $twilioNumber);

        // And the record should be in the database
        $this->assertDatabaseHas(NumberTwilio::TABLE_NAME, [
            'phone_number' => $phone_number
        ]);
    }

    /**
     * Test that SUT can find an active NumberTwilio
     *
     * @group CRM
     * @typeOfTest IntegrationTestCase
     *
     * @throws BindingResolutionException
     *
     * @covers \App\Repositories\CRM\Text\NumberRepository::findActiveTwilioNumber
     */
    public function testFindActiveTwilioNumber()
    {
        // Given that I have a number
        $number = $this->getFirstNumber();

        // When I call the repository to get said number
        $number = $this->getConcreteRepository()
            ->findActiveTwilioNumber($number->dealer_number, $number->customer_number);

        // I should get the right instance
        $this->assertInstanceOf(Number::class, $number);
    }

    /**
     * Test that SUT can find all Twilio Numbers for a given set of numbers
     *
     * @group CRM
     * @typeOfTest IntegrationTestCase
     *
     * @throws BindingResolutionException
     *
     * @covers \App\Repositories\CRM\Text\NumberRepository::findActiveTwilioNumber
     */
    public function testFindAllTwilioNumbers()
    {
        // Given that I have a number
        $number = $this->getFirstNumber();

        // When I call the repository to get all numbers
        $numbers = $this->getConcreteRepository()
            ->findAllTwilioNumbers($number->dealer_number, $number->customer_number);

        // I should get the same number of created numbers as the seeder, which is one
        $this->assertCount(1, $numbers);
    }

    /**
     * Test that SUT can check if the Twilio Number is active
     *
     * @group CRM
     * @typeOfTest IntegrationTestCase
     *
     * @covers \App\Repositories\CRM\Text\NumberRepository::activeTwilioNumber
     * @throws BindingResolutionException
     */
    public function testActiveTwilioNumber()
    {
        // Given that I have a twilio number and a customer number
        $number = $this->getFirstNumber();

        // And I call the repository
        $activeNumber = $this->getConcreteRepository()
            ->activeTwilioNumber($number->twilio_number, $number->customer_number);

        // I get the right instance
        $this->assertInstanceOf(Number::class, $activeNumber);
    }

    /**
     * Test that SUT can check if the Twilio Number is active by the Customer Number
     *
     * @group CRM
     * @typeOfTest IntegrationTestCase
     *
     * @throws BindingResolutionException
     *
     * @covers \App\Repositories\CRM\Text\NumberRepository::activeTwilioNumberByCustomerNumber
     */
    public function testActiveTwilioNumberByCustomerNumber()
    {
        // Given that I have a customer's phone
        $number = $this->getFirstNumber();

        // And I call the repository
        $foundNumber = $this->getConcreteRepository()->activeTwilioNumberByCustomerNumber(
            $number->customer_number,
            $this->seeder->dealer->getKey()
        );

        // I should get the right instance
        $this->assertInstanceOf(Number::class, $foundNumber);
    }

    /**
     * Test that SUT can delete a Twilio Number
     *
     * @group CRM
     * @typeOfTest IntegrationTestCase
     *
     * @throws BindingResolutionException
     *
     * @covers \App\Repositories\CRM\Text\NumberRepository::deleteTwilioNumber
     */
    public function testDeleteTwilioNumber()
    {
        // Given that I have a twilio number
        $twilioNumber = $this->getFirstTwilioNumber();

        // And I call delete on the repository
        $response = $this->getConcreteRepository()
            ->deleteTwilioNumber($twilioNumber->phone_number);

        // I should get true because it was deleted
        $this->assertTrue($response);

        // And the phone should no longer exist in the database
        $this->assertDatabaseMissing(NumberTwilio::TABLE_NAME, $twilioNumber->toArray());
    }

    /**
     * Test that SUT can get check if the given number is a dealer's number
     *
     * @group CRM
     * @typeOfTest IntegrationTestCase
     *
     * @throws BindingResolutionException
     *
     * @covers \App\Repositories\CRM\Text\NumberRepository::isDealerNumber
     */
    public function testIsDealerNumber()
    {
        // Given that I call the repository to check if it's a number
        $response = $this->getConcreteRepository()->isDealerNumber($this->seeder->dealerNumber);

        // It should find the seeded number and return true
        $this->assertTrue($response);
    }

    /**
     * Test that SUT can update the expiration date
     *
     * @group CRM
     * @typeOfTest IntegrationTestCase
     *
     * @throws BindingResolutionException
     *
     * @covers \App\Repositories\CRM\Text\NumberRepository::updateExpirationDate
     */
    public function testUpdateExpirationDate()
    {
        // Given that I have a Number
        $number = $this->getFirstNumber();

        // And I try to update the expiration date
        $response = $this->getConcreteRepository()
            ->updateExpirationDate(
                $this->expirationTime,
                $number->twilio_number,
                $number->dealer_number
            );

        // I get true, because the record was updated successfully
        $this->assertTrue($response);

        // And the expiration date was updated in the database
        $this->assertDatabaseHas(Number::TABLE_NAME, [
           'id' => $number->id,
           'expiration_time' => $this->expirationTime
        ]);
    }

    /**
     * Get the first from the created numbers
     *
     * @return Number
     */
    private function getFirstNumber(): Number
    {
        $numbers = $this->seeder->createdNumbers;
        return reset($numbers);
    }

    /**
     * Get the first from the created numbers
     *
     * @return NumberTwilio
     */
    private function getFirstTwilioNumber(): NumberTwilio
    {
        $twilioNumbers = $this->seeder->twilioNumbers;
        return reset($twilioNumbers);
    }

    /**
     * @return NumberRepositoryInterface
     * @throws BindingResolutionException when there is a problem with the resolution of the concrete class
     */
    protected function getConcreteRepository(): NumberRepositoryInterface
    {
        return $this->app->make(NumberRepositoryInterface::class);
    }
}
