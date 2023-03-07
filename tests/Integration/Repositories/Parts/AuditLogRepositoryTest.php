<?php

namespace Tests\Integration\Repositories\Parts;

use App\Models\Parts\AuditLog;
use App\Repositories\Parts\AuditLogRepository;
use Mockery\Mock;
use Tests\TestCase;

class AuditLogRepositoryTest extends TestCase
{
    /**
     * @group DMS
     * @group DMS_PARTS
     *
     * @return void
     * @throws \Exception
     */
    public function testCreatesRow()
    {
        /** @var AuditLogRepository $repo */
        $repo = app(AuditLogRepository::class);
        $model = $repo->create([
            'part_id' => 999990,
            'bin_id' => 1,
            'qty' => 2,
            'balance' => 10,
            'description' => 'Created by test',
        ]);

        $this->assertInstanceOf(AuditLog::class, $model);
        $this->assertNotEmpty($model->id);
        $this->assertSame(999990, $model->part_id);
        $this->assertSame(1, $model->bin_id);
        $this->assertSame(2, $model->qty);
        $this->assertSame(10, $model->balance);
        $this->assertSame('Created by test', $model->description);

        $this->assertDatabaseHas('parts_audit_log', [
            'part_id' => 999990,
            'bin_id' => 1,
        ]);

        $model->delete();
    }
}
