<?php

namespace Tests\Unit\Services\Dms\Pos;

use App\Exceptions\Dms\Pos\RegisterException;
use App\Models\Pos\Register;
use App\Repositories\Dms\Pos\RegisterRepositoryInterface;
use App\Services\Common\LoggerService;
use App\Services\Dms\Pos\RegisterService;
use App\Services\Dms\Pos\RegisterServiceInterface;
use Illuminate\Support\Facades\Log;
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
        $this->service = new RegisterService($this->repository);
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
        $register = $this->getEloquentMock(Register::class);
        $this->repository->shouldReceive('hasOpenRegister')
            ->once()
            ->with($this->requestPayload['outlet_id'])
            ->andReturn(false);

        $this->repository
            ->shouldReceive('beginTransaction')
            ->once();

        $this->repository->shouldReceive('create')
            ->once()
            ->with($this->requestPayload)
            ->andReturns($register);

        $this->repository
            ->shouldReceive('commitTransaction')
            ->once();

        Log::shouldReceive('info')
            ->with('Register has been successfully opened for outlet.', ['register' => $register]);

        $result = $this->service->open($this->requestPayload);

        $this->assertTrue($result);
    }

    public function testOpenNewRegisterFailed()
    {
        $exception = new \Exception();
        $this->repository->shouldReceive('hasOpenRegister')
            ->once()
            ->with($this->requestPayload['outlet_id'])
            ->andReturn(false);

        $this->repository
            ->shouldReceive('beginTransaction')
            ->once();

        $this->repository->shouldReceive('create')
            ->once()
            ->with($this->requestPayload)
            ->andThrows($exception);

        $this->repository
            ->shouldReceive('commitTransaction')
            ->never();

        $this->repository
            ->shouldReceive('rollbackTransaction')
            ->once();

        Log::shouldReceive('error')
            ->with('Register open error. Params - ' . json_encode($this->requestPayload), $exception->getTrace());

        $this->expectException(RegisterException::class);

        $result = $this->service->open($this->requestPayload);

        $this->assertNull($result);
    }
}
