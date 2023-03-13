<?php

namespace Tests\Unit\Services\Integration\Transaction;

use App\Repositories\Feed\TransactionExecuteQueueRepositoryInterface;
use App\Services\Integration\Transaction\Adapter\Utc\Inventory;
use App\Services\Integration\Transaction\TransactionService;
use App\Services\Integration\Transaction\Validation;
use Mockery;
use Tests\TestCase;

/**
 * Test for App\Services\Integration\Transaction\TransactionService
 *
 * Class TransactionServiceTest
 * @package Tests\Unit\Services\Integration\Transaction
 *
 * @coversDefaultClass \App\Services\Integration\Transaction\TransactionService
 */
class TransactionServiceTest extends TestCase
{
    private const VALID_XML = '<?xml version="1.0" encoding="UTF-8"?>
           <request>
                <transactions>
                    <transaction>
                        <action><![CDATA[addInventory]]></action>
                        <data>
                            <dealer_identifier><![CDATA[3-4230-C]]></dealer_identifier>
                            <location_identifier><![CDATA[3-4230-C]]></location_identifier>
                            <vin><![CDATA[7K51E3439PH908976]]></vin>
                            <year><![CDATA[2023]]></year>
                            <brand><![CDATA[Haulmark]]></brand>
                            <status><![CDATA[Available]]></status>
                            <color><![CDATA[WHITE]]></color>
                            <gvwr><![CDATA[18000]]></gvwr>
                            <cost><![CDATA[35271]]></cost>
                            <model><![CDATA[EGP8532R4]]></model>
                            <construction><![CDATA[steel_frame_aluminum]]></construction>
                            <category><![CDATA[car_racing]]></category>
                            <hitch_type><![CDATA[bumper]]></hitch_type>
                            <roof_type><![CDATA[flat]]></roof_type>
                            <nose_type><![CDATA[flat]]></nose_type>
                            <axles><![CDATA[3]]></axles>
                            <msrp><![CDATA[46205]]></msrp>
                            <length><![CDATA[34.0000]]></length>
                            <width><![CDATA[8.5000]]></width>
                            <height><![CDATA[2.83]]></height>
                            <description><![CDATA[]]></description>
                            <images>
                                <image0><![CDATA[http://dealer-cdn.com/showroom-files/trailerZ6a96d2ece.jpg]]></image0>
                            </images>
                        </data>
                    </transaction>
                </transactions>
           </request>';

    private const EMPTY_XML = '<?xml version="1.0" encoding="UTF-8"?>
           <request>
                <transactions>
                </transactions>
           </request>';

    private const EMPTY_TRANSACTION_XML = '<?xml version="1.0" encoding="UTF-8"?>
           <request></request>';

    private const EMPTY_DATA_XML = '<?xml version="1.0" encoding="UTF-8"?>
           <request>
                <transactions>
                    <transaction>
                        <action><![CDATA[addInventory]]></action>
                    </transaction>
                </transactions>
           </request>';

    private const EMPTY_ACTION_XML = '<?xml version="1.0" encoding="UTF-8"?>
           <request>
                <transactions>
                    <transaction>
                        <data>
                            <dealer_identifier><![CDATA[3-4230-C]]></dealer_identifier>
                            <location_identifier><![CDATA[3-4230-C]]></location_identifier>
                            <vin><![CDATA[7K51E3439PH908976]]></vin>
                            <year><![CDATA[2023]]></year>
                            <brand><![CDATA[Haulmark]]></brand>
                            <status><![CDATA[Available]]></status>
                        </data>
                    </transaction>
                </transactions>
           </request>';

    /**
     * @var Validation|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    private $validation;

    /**
     * @var TransactionExecuteQueueRepositoryInterface|Mockery\LegacyMockInterface|Mockery\MockInterface
     */
    private $transactionExecuteQueueRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->validation = Mockery::mock(Validation::class);
        $this->app->instance(Validation::class, $this->validation);

        $this->transactionExecuteQueueRepository = Mockery::mock(TransactionExecuteQueueRepositoryInterface::class);
        $this->app->instance(TransactionExecuteQueueRepositoryInterface::class, $this->transactionExecuteQueueRepository);
    }

    /**
     * @covers ::post
     * @dataProvider dataProvider
     * @return void
     */
    public function testPost(array $params)
    {
        $transactionData = [
            'data' => $params['data'],
            'api' => $params['integration_name'],
            'without_prepare_data' => true
        ];

        $config = new \SimpleXMLElement($params['data'], LIBXML_NOCDATA);
        $transaction = json_decode(json_encode($config->transactions), true)['transaction'];

        $inventoryAdapterMock = Mockery::mock(Inventory::class);

        /** @var TransactionService|Mockery\LegacyMockInterface|Mockery\MockInterface $service */
        $service = Mockery::mock(TransactionService::class,[$this->transactionExecuteQueueRepository, $this->validation])
            ->shouldAllowMockingProtectedMethods();

        $this->transactionExecuteQueueRepository
            ->shouldReceive('create')
            ->with($transactionData)
            ->once();

        $this->validation
            ->shouldReceive('setApiKey')
            ->with($params['integration_name'])
            ->once();

        $this->validation
            ->shouldReceive('isValidAction')
            ->with($transaction['action'])
            ->andReturn(true)
            ->once();

        $this->validation
            ->shouldReceive('validateTransaction')
            ->with($transaction['action'], $transaction['data'], '0', $service)
            ->once();

        $service
            ->shouldReceive('createAdapter')
            ->with(['action' => 'add', 'entity_type' => 'inventory'])
            ->andReturn($inventoryAdapterMock)
            ->once();

        $inventoryAdapterMock
            ->shouldReceive('add')
            ->with($transaction['data'])
            ->andReturn(true)
            ->once();

        $service->shouldReceive('post')->passthru();
        $service->shouldReceive('getTransactionErrors')->passthru();
        $service->shouldReceive('executeTransaction')->passthru();

        $result = $service->post($params);
        $result = new \SimpleXMLElement($result, LIBXML_NOCDATA);

        $this->assertSame('success', (string)$result->status);
        $this->assertSame('executed', (string)$result->transactions->transaction->status);

        $this->assertXmlStringEqualsXmlString(
            $config->transactions->transaction->data->asXML(),
            $result->transactions->transaction->original_transaction->data->asXML()
        );

        $errors = $service->getTransactionErrors();

        $this->assertIsArray($errors);
        $this->assertEmpty($errors);
    }


    /**
     * @covers ::post
     * @dataProvider dataProvider
     * @return void
     */
    public function testPostWithNotValidAction(array $params)
    {
        $transactionData = [
            'data' => $params['data'],
            'api' => $params['integration_name'],
            'without_prepare_data' => true
        ];

        $config = new \SimpleXMLElement($params['data'], LIBXML_NOCDATA);
        $transaction = json_decode(json_encode($config->transactions), true)['transaction'];

        $this->transactionExecuteQueueRepository
            ->shouldReceive('create')
            ->with($transactionData)
            ->once();

        $this->validation
            ->shouldReceive('setApiKey')
            ->with($params['integration_name'])
            ->once();

        $this->validation
            ->shouldReceive('isValidAction')
            ->with($transaction['action'])
            ->andReturn(false)
            ->once();

        $this->validation
            ->shouldReceive('validateTransaction')
            ->never();

        /** @var TransactionService|Mockery\LegacyMockInterface|Mockery\MockInterface $service */
        $service = Mockery::mock(TransactionService::class,[$this->transactionExecuteQueueRepository, $this->validation]);

        $service->shouldReceive('post')->passthru();
        $service->shouldReceive('addTransactionError')->passthru();
        $service->shouldReceive('getTransactionErrors')->passthru();

        $result = $service->post($params);
        $result = new \SimpleXMLElement($result, LIBXML_NOCDATA);

        $this->assertSame('error', (string)$result->status);

        $errors = $service->getTransactionErrors();

        $this->assertIsArray($errors);
        $this->assertNotEmpty($errors);
        $this->assertSame('Invalid action "' . $transaction['action'] . '" supplied for this transaction.', $errors[0][0]);
    }

    /**
     * @covers ::post
     * @dataProvider emptyTransactionDataProvider
     * @return void
     */
    public function testPostWithEmptyTransaction(array $params, string $expectedError)
    {
        $transactionData = [
            'data' => $params['data'],
            'api' => $params['integration_name'],
            'without_prepare_data' => true
        ];

        $this->transactionExecuteQueueRepository
            ->shouldReceive('create')
            ->with($transactionData)
            ->once();

        $this->validation
            ->shouldReceive('setApiKey')
            ->never();

        $this->validation
            ->shouldReceive('validateTransaction')
            ->never();

        /** @var TransactionService $service */
        $service = app()->make(TransactionService::class);

        $result = $service->post($params);
        $result = new \SimpleXMLElement($result, LIBXML_NOCDATA);

        $this->assertSame('error', (string)$result->status);

        $errors = $service->getTransactionErrors();

        $this->assertIsArray($errors);
        $this->assertNotEmpty($errors);
        $this->assertSame($expectedError, $errors[0][0]);
    }

    /**
     * @covers ::post
     * @dataProvider emptyDataDataProvider
     * @return void
     */
    public function testPostWithEmptyData(array $params, string $expectedError)
    {
        $transactionData = [
            'data' => $params['data'],
            'api' => $params['integration_name'],
            'without_prepare_data' => true
        ];

        $this->transactionExecuteQueueRepository
            ->shouldReceive('create')
            ->with($transactionData)
            ->once();

        $this->validation
            ->shouldReceive('setApiKey')
            ->with($params['integration_name'])
            ->once();

        $this->validation
            ->shouldReceive('validateTransaction')
            ->never();

        /** @var TransactionService $service */
        $service = app()->make(TransactionService::class);

        $result = $service->post($params);
        $result = new \SimpleXMLElement($result, LIBXML_NOCDATA);

        $this->assertSame('error', (string)$result->status);

        $errors = $service->getTransactionErrors();

        $this->assertIsArray($errors);
        $this->assertNotEmpty($errors);
        $this->assertSame($expectedError, $errors[0][0]);
    }

    /**
     * @return array[]
     */
    public function dataProvider(): array
    {
        return [
            [[
                'integration_name' => 'utc',
                'data' => self::VALID_XML,
            ]]
        ];
    }

    /**
     * @return array[]
     */
    public function emptyDataDataProvider(): array
    {
        return [
            [
                [
                    'integration_name' => 'utc',
                    'data' => self::EMPTY_DATA_XML,
                ],
                'No data supplied for this transaction.'
            ],
            [
                [
                    'integration_name' => 'utc',
                    'data' => self::EMPTY_ACTION_XML,
                ],
                'No action supplied for this transaction.'
            ],
        ];
    }

    /**
     * @return array[]
     */
    public function emptyTransactionDataProvider(): array
    {
        return [
            [
                [
                    'integration_name' => 'utc',
                    'data' => self::EMPTY_XML,
                ],
                'No data supplied for this transaction.'
            ],
            [
                [
                    'integration_name' => 'utc',
                    'data' => self::EMPTY_TRANSACTION_XML,
                ],
                'No data supplied for this transaction.'
            ],
        ];
    }
}
