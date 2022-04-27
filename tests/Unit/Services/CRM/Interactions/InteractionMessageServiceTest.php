<?php

namespace Tests\Unit\Services\CRM\Interactions;

use App\Exceptions\CRM\Interactions\InteractionMessageException;
use App\Models\CRM\Interactions\InteractionMessage;
use App\Repositories\CRM\Interactions\InteractionMessageRepositoryInterface;
use App\Services\CRM\Interactions\InteractionMessageService;
use Illuminate\Support\Facades\Log;
use Mockery;
use Mockery\LegacyMockInterface;
use Tests\TestCase;

/**
 * Class InteractionMessageServiceTest
 * @package Tests\Unit\Services\CRM\Interactions
 *
 * @coversDefaultClass \App\Services\CRM\Interactions\InteractionMessageService
 */
class InteractionMessageServiceTest extends TestCase
{
    /**
     * @var InteractionMessageRepositoryInterface|LegacyMockInterface
     */
    private $interactionMessageRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->interactionMessageRepository = Mockery::mock(InteractionMessageRepositoryInterface::class);
        $this->app->instance(InteractionMessageRepositoryInterface::class, $this->interactionMessageRepository);
    }

    /**
     * @group CRM
     * @dataProvider bulkUpdateParamsProvider
     * @covers ::bulkUpdate
     */
    public function testBulkUpdateWithoutSearchParams($expectedBulkUpdateParams, $expectedSearchParams, $searchReturned)
    {
        $this->interactionMessageRepository
            ->shouldReceive('search')
            ->never();

        $this->interactionMessageRepository
            ->shouldReceive('bulkUpdate')
            ->once()
            ->with($expectedBulkUpdateParams)
            ->andReturn(true);

        /** @var InteractionMessageService $service */
        $service = $this->app->make(InteractionMessageService::class);

        $result = $service->bulkUpdate($expectedBulkUpdateParams);

        $this->assertTrue($result);
    }

    /**
     * @group CRM
     * @dataProvider bulkUpdateParamsProvider
     * @covers ::bulkUpdate
     */
    public function testBulkUpdateWithSearchParams($expectedBulkUpdateParams, $expectedSearchParams, $searchReturned)
    {
        $params = array_merge($expectedBulkUpdateParams, $expectedSearchParams);

        $searchReturnedIds = array_column($searchReturned, 'id');

        $this->interactionMessageRepository
            ->shouldReceive('search')
            ->once()
            ->with($expectedSearchParams['search_params'])
            ->andReturn($searchReturned);

        $this->interactionMessageRepository
            ->shouldReceive('bulkUpdate')
            ->once()
            ->with(array_merge(['ids' => $searchReturnedIds], $expectedBulkUpdateParams))
            ->andReturn(true);

        /** @var InteractionMessageService $service */
        $service = $this->app->make(InteractionMessageService::class);

        $result = $service->bulkUpdate($params);

        $this->assertTrue($result);
    }

    /**
     * @group CRM
     * @dataProvider bulkUpdateParamsProvider
     * @covers ::bulkUpdate
     */
    public function testBulkUpdateInteractionMessagesNotFound($expectedBulkUpdateParams, $expectedSearchParams, $searchReturned)
    {
        $params = array_merge($expectedBulkUpdateParams, $expectedSearchParams);

        $this->interactionMessageRepository
            ->shouldReceive('search')
            ->once()
            ->with($expectedSearchParams['search_params'])
            ->andReturn([]);

        $this->interactionMessageRepository
            ->shouldReceive('bulkUpdate')
            ->never();

        /** @var InteractionMessageService $service */
        $service = $this->app->make(InteractionMessageService::class);

        $result = $service->bulkUpdate($params);

        $this->assertFalse($result);
    }

    /**
     * @group CRM
     * @dataProvider bulkUpdateParamsProvider
     * @covers ::bulkUpdate
     */
    public function testBulkUpdateWithError($expectedBulkUpdateParams, $expectedSearchParams, $searchReturned)
    {
        $this->expectException(InteractionMessageException::class);

        $exception = new \Exception('some message');
        $params = array_merge($expectedBulkUpdateParams, $expectedSearchParams);

        $this->interactionMessageRepository
            ->shouldReceive('search')
            ->once()
            ->with($expectedSearchParams['search_params'])
            ->andThrow($exception);

        $this->interactionMessageRepository
            ->shouldReceive('bulkUpdate')
            ->never();

        Log::shouldReceive('error')
            ->with('Interaction message bulk update error. Message - ' . $exception->getMessage() , $exception->getTrace());

        /** @var InteractionMessageService $service */
        $service = $this->app->make(InteractionMessageService::class);

        $service->bulkUpdate($params);
    }

    /**
     * @group CRM
     * @dataProvider bulkUpdateParamsProvider
     * @covers ::bulkSearchable
     */
    public function testSearchableWithoutSearchParams($expectedBulkUpdateParams, $expectedSearchParams, $searchReturned)
    {
        /** @var InteractionMessage|LegacyMockInterface $interactionMessage */
        $interactionMessage = $this->getEloquentMock(InteractionMessage::class);

        /** @var InteractionMessageService $service */
        $service = $this->app->make(InteractionMessageService::class);

        $this->interactionMessageRepository
            ->shouldReceive('search')
            ->never();

        $this->interactionMessageRepository
            ->shouldReceive('getAll')
            ->once()
            ->with($expectedBulkUpdateParams)
            ->andReturn($interactionMessage);

        $interactionMessage
            ->shouldReceive('searchable')
            ->once()
            ->withNoArgs();

        $result = $service->bulkSearchable($expectedBulkUpdateParams);

        $this->assertTrue($result);
    }

    /**
     * @group CRM
     * @dataProvider bulkUpdateParamsProvider
     * @covers ::bulkSearchable
     */
    public function testSearchableWithSearchParams($expectedBulkUpdateParams, $expectedSearchParams, $searchReturned)
    {
        /** @var InteractionMessage|LegacyMockInterface $interactionMessage */
        $interactionMessage = $this->getEloquentMock(InteractionMessage::class);

        $params = array_merge($expectedBulkUpdateParams, $expectedSearchParams);

        $searchReturnedIds = array_column($searchReturned, 'id');

        /** @var InteractionMessageService $service */
        $service = $this->app->make(InteractionMessageService::class);

        $this->interactionMessageRepository
            ->shouldReceive('search')
            ->once()
            ->with($expectedSearchParams['search_params'])
            ->andReturn($searchReturned);

        $this->interactionMessageRepository
            ->shouldReceive('getAll')
            ->once()
            ->with(array_merge(['ids' => $searchReturnedIds], $expectedBulkUpdateParams))
            ->andReturn($interactionMessage);

        $interactionMessage
            ->shouldReceive('searchable')
            ->once()
            ->withNoArgs();

        $result = $service->bulkSearchable($params);

        $this->assertTrue($result);
    }

    /**
     * @group CRM
     * @dataProvider bulkUpdateParamsProvider
     * @covers ::bulkSearchable
     */
    public function testSearchableMessagesNotFound($expectedBulkUpdateParams, $expectedSearchParams, $searchReturned)
    {
        /** @var InteractionMessage|LegacyMockInterface $interactionMessage */
        $interactionMessage = $this->getEloquentMock(InteractionMessage::class);

        $params = array_merge($expectedBulkUpdateParams, $expectedSearchParams);

        /** @var InteractionMessageService $service */
        $service = $this->app->make(InteractionMessageService::class);

        $this->interactionMessageRepository
            ->shouldReceive('search')
            ->once()
            ->with($expectedSearchParams['search_params'])
            ->andReturn([]);

        $this->interactionMessageRepository
            ->shouldReceive('bulkUpdate')
            ->never();

        $interactionMessage
            ->shouldReceive('searchable')
            ->never();

        $result = $service->bulkSearchable($params);

        $this->assertFalse($result);
    }

    /**
     * @group CRM
     * @dataProvider bulkUpdateParamsProvider
     * @covers ::bulkSearchable
     */
    public function testSearchableWithError($expectedBulkUpdateParams, $expectedSearchParams, $searchReturned)
    {
        $this->expectException(InteractionMessageException::class);

        $exception = new \Exception('some message');

        /** @var InteractionMessage|LegacyMockInterface $interactionMessage */
        $interactionMessage = $this->getEloquentMock(InteractionMessage::class);

        $params = array_merge($expectedBulkUpdateParams, $expectedSearchParams);

        /** @var InteractionMessageService $service */
        $service = $this->app->make(InteractionMessageService::class);

        $this->interactionMessageRepository
            ->shouldReceive('search')
            ->once()
            ->with($expectedSearchParams['search_params'])
            ->andThrow($exception);

        $this->interactionMessageRepository
            ->shouldReceive('bulkUpdate')
            ->never();

        $interactionMessage
            ->shouldReceive('searchable')
            ->never();

        Log::shouldReceive('error')
            ->with('Interaction message bulk searchable error. Message - ' . $exception->getMessage() , $exception->getTrace());

        $service->bulkSearchable($params);
    }

    /**
     * @return \array[][]
     */
    public function bulkUpdateParamsProvider(): array
    {
        return [[
            [
                'hidden' => 1, 'is_read' => 1
            ],
            [
                'search_params' => [
                    'lead_id' => PHP_INT_MAX, 'size' => 10000
                ]
            ],
            [
                [
                    'id' => 123456,
                    'some_field' => 'some_value'
                ],
                [
                    'id' => 654321,
                    'some_field2' => 'some_value2'
                ]
            ]
        ]];
    }
}
