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
    private $registerRepositoryMock;

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
        $this->registerRepositoryMock = Mockery::mock(RegisterRepositoryInterface::class);
        $this->service = new RegisterService($this->registerRepositoryMock);
    }

    /**
     * @group DMS
     * @group DMS_POS
     *
     * @return void
     * @throws RegisterException
     */
    public function testRegisterIsOpen()
    {
        $this->registerRepositoryMock->shouldReceive('hasOpenRegister')
            ->once()
            ->with($this->requestPayload['outlet_id'])
            ->andReturn(true);

        $result = $this->service->open($this->requestPayload);

        $this->assertSame('A register is already opened!', $result);
    }

    /**
     * @group DMS
     * @group DMS_POS
     *
     * @return void
     * @throws RegisterException
     */
    public function testOpenNewRegisterSuccessful()
    {
        $register = $this->getEloquentMock(Register::class);
        $this->registerRepositoryMock->shouldReceive('hasOpenRegister')
            ->once()
            ->with($this->requestPayload['outlet_id'])
            ->andReturn(false);

        $this->registerRepositoryMock
            ->shouldReceive('beginTransaction')
            ->once();

        $this->registerRepositoryMock->shouldReceive('create')
            ->once()
            ->with($this->requestPayload)
            ->andReturns($register);

        $this->registerRepositoryMock
            ->shouldReceive('commitTransaction')
            ->once();

        Log::shouldReceive('info')
            ->with('Register has been successfully opened for outlet.', ['register' => $register]);

        $result = $this->service->open($this->requestPayload);

        $this->assertSame('Register has been opened successfully!', $result);
    }

    /**
     * @group DMS
     * @group DMS_POS
     *
     * @return void
     * @throws RegisterException
     */
    public function testOpenNewRegisterFailed()
    {
        $exception = new \Exception();
        $this->registerRepositoryMock->shouldReceive('hasOpenRegister')
            ->once()
            ->with($this->requestPayload['outlet_id'])
            ->andReturn(false);

        $this->registerRepositoryMock
            ->shouldReceive('beginTransaction')
            ->once();

        $this->registerRepositoryMock->shouldReceive('create')
            ->once()
            ->with($this->requestPayload)
            ->andThrows($exception);

        $this->registerRepositoryMock
            ->shouldReceive('commitTransaction')
            ->never();

        $this->registerRepositoryMock
            ->shouldReceive('rollbackTransaction')
            ->once();

        Log::shouldReceive('error')
            ->with('Register open error. Params - ' . json_encode($this->requestPayload), $exception->getTrace());

        $this->expectException(RegisterException::class);

        $result = $this->service->open($this->requestPayload);

        $this->assertNull($result);
    }
}
