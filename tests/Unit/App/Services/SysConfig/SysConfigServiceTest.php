<?php

namespace Tests\Unit\App\Services\SysConfig;

use App\Models\SysConfig\SysConfig;
use App\Repositories\SysConfig\SysConfigRepositoryInterface;
use App\Services\SysConfig\SysConfigService;
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\Common\TestCase;

class SysConfigServiceTest extends TestCase
{
    private SysConfigService $service;
    private MockObject $sysConfigRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = $this->getConcreteService();
    }

    public function testList()
    {
        $this->sysConfigRepository->expects($this->once())
            ->method('getAll')
            ->will($this->returnValue($this->sysConfigFixture()));
        $response = $this->service->list();
        $this->assertEquals($response, [
            'filter' => [
                'size' => [
                    'length' => [
                        'min' => '3',
                        'max' => '100',
                    ],
                ],
            ],
        ]);
    }

    private function getConcreteService(): SysConfigService
    {
        $this->sysConfigRepository = $this->mockSysConfigRepository();

        return new SysConfigService($this->sysConfigRepository);
    }

    private function mockSysConfigRepository(): MockObject
    {
        return $this->createMock(SysConfigRepositoryInterface::class);
    }

    private function sysConfigFixture()
    {
        return new Collection([
            new SysConfig([
               'key' => 'filter/size/length/min',
               'value' => '3',
            ]),
            new SysConfig([
                'key' => 'filter/size/length/max',
                'value' => '100',
            ]),
        ]);
    }
}
