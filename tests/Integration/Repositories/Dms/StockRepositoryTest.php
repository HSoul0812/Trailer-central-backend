<?php

declare(strict_types=1);

namespace Tests\Integration\Repositories\Dms;

use App\Repositories\Dms\StockRepository;
use App\Repositories\Dms\StockRepositoryInterface;
use Exception;
use Faker\Factory as Faker;
use Faker\Generator;
use Illuminate\Contracts\Container\BindingResolutionException;
use InvalidArgumentException;
use Tests\database\seeds\Dms\StocksSeeder;
use Tests\TestCase;

/**
 * @covers \App\Repositories\Dms\StockRepository
 */
class StockRepositoryTest extends TestCase
{
    /**
     * @var StocksSeeder
     */
    protected $seeder;

    /**
     * @var Generator
     */
    protected $faker;

    /**
     * Test that SUT is properly bound by the application
     *
     * @group DMS
     * @group DMS_STOCK
     *
     * @throws BindingResolutionException when there is a problem with resolution of concreted class
     */
    public function testServiceBindingIsWorking(): void
    {
        $concreteRepository = $this->getConcreteRepository();

        self::assertInstanceOf(StockRepository::class, $concreteRepository);
    }

    /**
     * Test that SUT is throwing a PDOException when some constraint is not being satisfied
     *
     * @covers ::financialReport
     *
     * @group DMS
     * @group DMS_STOCK
     *
     * @throws BindingResolutionException when there is a problem with resolution of concreted class
     */
    public function testFinancialReportWillThrowAnException(): void
    {
        // Given I've got the concrete repository for "StockRepositoryInterface" from the application service container

        // When I call `financialReport` without a dealer identifier
        // Then I expect see that one exception have been thrown with a specific message
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'dealer_id' argument is required");

        /** @var null $report */
        $report = $this->getConcreteRepository()->financialReport([]);

        // And I should get a null value
        self::assertNull($report);
    }

    /**
     * @dataProvider queryParametersAndSummariesProvider
     *
     * @covers ::financialReport
     *
     * @group DMS
     * @group DMS_STOCK
     *
     * @param array $params list of query parameters
     * @param callable|int|string $numberOfRowsExpected
     * @param callable|int|string $lastRowTitleExpected
     * @throws BindingResolutionException when there is a problem with resolution of concreted class
     * @throws Exception when `random_int` was not able to gather sufficient entropy.
     */
    public function testFinancialReportIsFilteringAsExpected(
        array $params,
        $numberOfRowsExpected,
        $lastRowTitleExpected
    ): void
    {
        // Given I have few stocks
        $this->seeder->seed();

        $extractedParams = $this->seeder->extractValues($params);

        $numberOfRowsExpected = is_callable($numberOfRowsExpected) ? $numberOfRowsExpected($this->seeder) : $numberOfRowsExpected;
        $lastRowTitleExpected = is_callable($lastRowTitleExpected) ? $lastRowTitleExpected($this->seeder) : $lastRowTitleExpected;

        // And I've got the concrete repository for "StockRepositoryInterface" from the application service container
        // When I call the method `financialReport` with a some invalid parameters
        $reportFromRepo = $this->getConcreteRepository()->financialReport($extractedParams);

        // Then I should get an array from `financialReport`
        self::assertIsArray($reportFromRepo);//dd($reportFromRepo);
        // And I should see the number of rows expected is the same as retrieved from `financialReport`
        self::assertCount($numberOfRowsExpected, $reportFromRepo);
        // And I should see the last row title expected is the same as retrieved from `financialReport`
        $lastRowOfReport = end($reportFromRepo);
        self::assertSame($lastRowTitleExpected, end($lastRowOfReport)['part']->title);
    }

    /**
     * Examples of parameters, number of rows expected and last row title expected.
     *
     * @return array<string, array>
     */
    public function queryParametersAndSummariesProvider(): array
    {
        return [                                            // array $parameters, int $numberOfRowsExpected, int $lastRowTitleExpected
            'By dummy dealer and empty type of stock'     => [['dealer_id' => $this->getSeededData(0, 'dealer_id')], $this->getSeededData(0, 'number_of_rows'), $this->getSeededData(0, 'last_row_title')],
            'By dummy dealer and mixed stocks'            => [['dealer_id' => $this->getSeededData(0, 'dealer_id'), 'type_of_stock' => StockRepository::STOCK_TYPE_MIXED], $this->getSeededData(0, 'number_of_rows', StockRepository::STOCK_TYPE_MIXED), $this->getSeededData(0, 'last_row_title', StockRepository::STOCK_TYPE_MIXED)],
            'By second dummy dealer and only parts'       => [['dealer_id' => $this->getSeededData(1, 'dealer_id'), 'type_of_stock' => StockRepository::STOCK_TYPE_PARTS], $this->getSeededData(1, 'number_of_rows', StockRepository::STOCK_TYPE_PARTS), $this->getSeededData(1, 'last_row_title', StockRepository::STOCK_TYPE_PARTS)],
            'By second dummy dealer and only major units' => [['dealer_id' => $this->getSeededData(1, 'dealer_id'), 'type_of_stock' => StockRepository::STOCK_TYPE_INVENTORIES], $this->getSeededData(1, 'number_of_rows', StockRepository::STOCK_TYPE_INVENTORIES), $this->getSeededData(1, 'last_row_title', StockRepository::STOCK_TYPE_INVENTORIES)],
            'By dummy dealer filtering by title'          => [['dealer_id' => $this->getSeededData(0, 'dealer_id'), 'search_term' => 'Duper part'], 2, 'Super duper part']
        ];
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->faker = $this->faker ?? Faker::create();

        $this->seeder = $this->seeder ?? new StocksSeeder();
    }

    public function tearDown(): void
    {
        $this->seeder->cleanUp();

        parent::tearDown();
    }

    /**
     * @return StockRepositoryInterface
     *
     * @throws BindingResolutionException when there is a problem with resolution of concreted class
     *
     */
    protected function getConcreteRepository(): StockRepositoryInterface
    {
        return $this->app->make(StockRepositoryInterface::class);
    }

    /**
     * @param int $dealerIndex the array index of a dealer
     * @param string $keyName the key name of the needed value
     * @param string $typeOfReport
     * @return callable
     */
    protected function getSeededData(int $dealerIndex, string $keyName, string $typeOfReport = ''): callable
    {
        $this->faker = Faker::create();

        /**
         * @param StocksSeeder $seeder
         * @return mixed
         */
        return static function (StocksSeeder $seeder) use ($dealerIndex, $keyName, $typeOfReport) {
            $dealerId = $seeder->dealers[$dealerIndex]->getKey();

            switch ($keyName) {
                case 'dealer_id':
                    return $dealerId;
                case 'number_of_rows':
                    return count($seeder->buildReport($dealerIndex, $typeOfReport));
                case 'last_row_title':
                    $report = $seeder->buildReport($dealerIndex, $typeOfReport);
                    $lastRow = end($report);

                    return end($lastRow)['part']->title;
                case 'all':
                    return $seeder->buildReport($dealerIndex, $typeOfReport);
            }

            return null;
        };
    }
}
