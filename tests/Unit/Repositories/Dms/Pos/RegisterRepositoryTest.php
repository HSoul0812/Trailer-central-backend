<?php

namespace Tests\Unit\Repositories\Dms\Pos;

use App\Models\Pos\Outlet;
use App\Repositories\Dms\Pos\RegisterRepository;
use Mockery;
use Tests\TestCase;

class RegisterRepositoryTest extends TestCase
{
    /**
     * @group DMS
     * @group DMS_POS
     *
     * @return void
     */
    public function testGetAllByDealerId()
    {
        $registers = [
            ['id' => 1, 'config' => '{genius:ipAddress}', 'register_name' => 'Test Register 1'],
            ['id' => 2, 'config' => '{genius:ipAddress}', 'register_name' => 'Test Register 2'],
        ];
        $result = new \Illuminate\Database\Eloquent\Collection($registers);
        $outletMock = Mockery::mock(Outlet::class);
        $outletMock
            ->shouldReceive('select')
            ->once()
            ->andReturn($outletMock);
        $outletMock
            ->shouldReceive('join')
            ->once()
            ->andReturn($outletMock);
        $outletMock
            ->shouldReceive('where')
            ->with('crm_pos_outlet.dealer_id', 1)
            ->once()
            ->andReturn($outletMock);
        $outletMock
            ->shouldReceive('whereNull')
            ->once()
            ->andReturn($outletMock);
        $outletMock
            ->shouldReceive('orderBy')
            ->once()
            ->andReturn($outletMock);
        $outletMock
            ->shouldReceive('get')
            ->once()
            ->andReturn($result);

        $registerRepository = new RegisterRepository($outletMock);
        $response = $registerRepository->getAllByDealerId(1);

        $this->assertSame($registers, $response->all());
        $this->assertCount(2, $response);
    }
}
