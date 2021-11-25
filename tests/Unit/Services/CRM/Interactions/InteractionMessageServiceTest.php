<?php

namespace Tests\Unit\Services\CRM\Interactions;

use App\Models\CRM\Interactions\InteractionMessage;
use App\Repositories\CRM\Interactions\InteractionMessageRepositoryInterface;
use App\Services\CRM\Interactions\InteractionMessageService;
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
     * @covers ::bulkUpdate
     */
    public function testBulkUpdateWithoutSearchParams()
    {
        $params = ['hidden' => 1, 'is_read' => 1];

        $this->interactionMessageRepository
            ->shouldReceive('bulkUpdate')
            ->once()
            ->with($params)
            ->andReturn(true);

        /** @var InteractionMessageService $service */
        $service = $this->app->make(InteractionMessageService::class);

        $result = $service->bulkUpdate($params);

        $this->assertTrue($result);
    }

    public function testBulkUpdateWhitSearchParams()
    {
        $expectedBulkUpdateParams = ['hidden' => 1, 'is_read' => 1];
        $expectedSearchParams = ['search_params' => ['lead_id' => PHP_INT_MAX, 'size' => 10000]];

        $params = array_merge($expectedBulkUpdateParams, $expectedSearchParams);



        /** @var InteractionMessageService $service */
        $service = $this->app->make(InteractionMessageService::class);

        $result = $service->bulkUpdate($params);

        $this->assertTrue($result);
    }

    public function testSearchable()
    {
        /** @var InteractionMessage|LegacyMockInterface $interactionMessage */
        $interactionMessage = $this->getEloquentMock(InteractionMessage::class);

        /** @var InteractionMessageService $service */
        $service = $this->app->make(InteractionMessageService::class);
    }
}
