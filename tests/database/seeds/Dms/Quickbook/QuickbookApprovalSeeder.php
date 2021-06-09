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
 * @property-read QuickbookApproval $qbApproval
 */
class QuickbookApprovalSeeder extends Seeder
{
    use WithGetter;

    /**
     * @var QuickbookApproval
     */
    private $qbApproval;

    /**
     * @var User
     */
    private $dealer;

    public function seed(): void
    {
        $latestApproval = QuickbookApproval::orderBy('id', 'desc')->first();
        $this->qbApproval = factory(QuickbookApproval::class)->create([
            'id' => $latestApproval->id + 2,
            'dealer_id' => 1001,
        ]);
    }

    public function cleanUp(): void
    {
        QuickbookApproval::where(['id' => $this->qbApproval->id])->delete();
    }
}
