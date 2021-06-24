<?php

namespace Tests\Unit\Services\Dms\Pos;

use App\Repositories\Dms\Pos\RegisterRepositoryInterface;
use App\Services\Common\LoggerService;
use App\Services\Dms\Pos\RegisterService;
use App\Services\Dms\Pos\RegisterServiceInterface;
use Mockery;
use Tests\TestCase;

class RegisterServiceTest extends TestCase
{
    /**
     * @var RegisterRepositoryInterface
     */
    private $repository;

    /**
     * @var RegisterServiceInterface
     */
    private $service;

    private $requestPayload = [
        'outlet_id' => 62,
        'floating_amount' => 500.25,
        'open_notes' => 'Opening float',
    ];

    public function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(RegisterRepositoryInterface::class);
        $this->service = new RegisterService($this->repository, new LoggerService());
    }

    public function testRegisterIsOpen()
    {
        $this->repository->shouldReceive('hasOpenRegister')
            ->once()
            ->with($this->requestPayload['outlet_id'])
            ->andReturn(true);

        $result = $this->service->open($this->requestPayload);

        $this->assertTrue($result);
    }

    public function testOpenNewRegisterSuccessful()
    {
        $this->repository->shouldReceive('hasOpenRegister')
            ->once()
            ->with($this->requestPayload['outlet_id'])
            ->andReturn(false);

        $this->repository->shouldReceive('create')
            ->once()
            ->with($this->requestPayload)
            ->andReturns(true);
        $result = $this->service->open($this->requestPayload);

        $this->assertTrue($result);
    }

    public function testOpenNewRegisterFailed()
    {
        $this->repository->shouldReceive('hasOpenRegister')
            ->once()
            ->with($this->requestPayload['outlet_id'])
            ->andReturn(false);

        $this->repository->shouldReceive('create')
            ->once()
            ->with($this->requestPayload)
            ->andReturns(false);
        $result = $this->service->open($this->requestPayload);

        $this->assertFalse($result);
    }
}
