<?php
namespace Tests\database\seeds\Dms\Quickbook;

use App\Models\CRM\Dms\Quickbooks\QuickbookApproval;
use App\Models\CRM\Dms\Quickbooks\QuickbookApprovalDeleted;
use App\Models\User\User;
use App\Traits\WithGetter;
use Tests\database\seeds\Seeder;

/**
 * Class QuickbookApprovalSeeder
 * @package Tests\database\seeds\Integration
 *
 * @property-read User $dealer
 * @property-read QuickbookApprovalDeleted $qbApproval
 */
class QuickbookApprovalDeletedSeeder extends Seeder
{
    use WithGetter;

    /**
     * @var QuickbookApprovalDeleted
     */
    private $qbApprovalDeleted;

    /**
     * @var User
     */
    private $dealer;

    public function seed(): void
    {
        $latestDeleted = factory(QuickbookApprovalDeleted::class)->create();
        $this->qbApprovalDeleted = factory(QuickbookApprovalDeleted::class)->create([
            'id' => $latestDeleted->id + 2,
            'dealer_id' => 1001,
            'removed_by' => 1001,
            'deleted_at' => new \DateTime()
        ]);
    }

    public function cleanUp(): void
    {
        QuickbookApprovalDeleted::where(['id' => $this->qbApprovalDeleted->id])->delete();
    }
}
