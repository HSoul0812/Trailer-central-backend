<?php
namespace Tests\Integration\Repositories\Dms\Quickbook;

use App\Repositories\Dms\Quickbooks\QuickbookApprovalDeletedRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * @covers \App\Repositories\Dms\Quickbooks\QuickbookApprovalRepository
 */
class QuickbookApprovalDeletedRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    public function testGetAll()
    {
        $repository = app(QuickbookApprovalDeletedRepository::class);
        $qbs = $repository->getAll(['dealer_id' => 1001, 'page' => 1, 'sort' => 'created_at']);

        $qbDeleted = $qbs[0];

        $this->assertSame(1001, $qbDeleted->dealer_id);
    }
}
