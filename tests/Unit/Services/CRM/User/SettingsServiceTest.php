<?php

namespace Tests\Unit\Services\CRM\User;

use Mockery;
use Tests\TestCase;
use Mockery\LegacyMockInterface;
use App\Repositories\CRM\User\CrmUserRepositoryInterface;
use App\Repositories\CRM\User\SettingsRepositoryInterface;
use App\Services\CRM\User\SettingsServiceInterface;
use App\Models\User\CrmUser;

/**
 * Test for App\Services\CRM\User\SettingsService
 *
 * Class SettingsServiceTest
 * @package Tests\Unit\Services\CRM\User
 *
 * @coversDefaultClass \App\Services\CRM\User\SettingsService
 */
class SettingsServiceTest extends TestCase 
{
    const USERID = PHP_INT_MAX - 1;
    /**
     * @var LegacyMockInterface|CrmUserRepositoryInterface
     */
    protected $crmUserRepositoryMock;

    /**
     * @var LegacyMockInterface|SettingsRepositoryInterface
     */
    protected $crmSettingRepositoryMock;

    public function setUp(): void
    {
        parent::setUp();

        $this->instanceMock('crmUserRepositoryMock', CrmUserRepositoryInterface::class);

        $this->instanceMock('crmSettingRepositoryMock', SettingsRepositoryInterface::class);
    }

    /**
     * @group CRM
     */
    public function testGetAll()
    {
        $params = ['user_id' => self::USERID];

        $this->crmUserRepositoryMock->shouldReceive('get')
            ->once()->with($params)
            ->andReturn($this->getEloquentMock(CrmUser::class));

        $service = app(SettingsServiceInterface::class);

        $result = $service->getAll($params);

        $this->assertInstanceOf(CrmUser::class, $result);
    }

    /**
     * @group CRM
     */
    public function testUpdate()
    {
        $params = [
            'enable_hot_potato' => 0,
            'disable_daily_digest' => 1,
            'enable_assign_notification' => 0,
            'default/filters/sort' => 1,
            'round-robin/hot-potato/skip-weekends' => 0,
            'round-robin/hot-potato/use-submission-date' => 1,
            'unregistered_additional_field1' => 111,
            'unregistered_additional_field2' => 222,
            'user_id' => self::USERID
        ];

        $this->crmUserRepositoryMock->shouldReceive('update')
            ->once()->with([
                'user_id' => self::USERID,
                'enable_hot_potato' => 0,
                'disable_daily_digest' => 1,
                'enable_assign_notification' => 0,
            ]);

        $this->crmSettingRepositoryMock->shouldReceive('update')
            ->once()->with([
                'user_id' => self::USERID,
                'default/filters/sort' => 1,
                'round-robin/hot-potato/skip-weekends' => 0,
                'round-robin/hot-potato/use-submission-date' => 1,
            ]);

        $service = app(SettingsServiceInterface::class);

        $result = $service->update($params);

        $this->assertEquals($result, [
            'enable_hot_potato' => 0,
            'disable_daily_digest' => 1,
            'enable_assign_notification' => 0,
            'default/filters/sort' => 1,
            'round-robin/hot-potato/skip-weekends' => 0,
            'round-robin/hot-potato/use-submission-date' => 1,
        ]);
    }
}