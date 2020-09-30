<?php

namespace App\Console\Commands\CRM\Dms;

use App\Repositories\Dms\Quickbooks\QuickbookApprovalRepositoryInterface;
use Illuminate\Console\Command;
use App\Models\CRM\Dms\Quickbooks\QuickbookApproval;
use App\Models\CRM\Dms\Quickbooks\Preference;
use App\Repositories\User\UserRepositoryInterface;

class UpdatePoNumRef extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crm:dms:update-po-num-ref';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update all invoice approvals with PO # which are not approved yet.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(QuickbookApprovalRepositoryInterface $qbApprovalRepository, UserRepositoryInterface $userRepository)
    {
        $dmsActiveDealers = $userRepository->getDmsActiveUsers();
        
        foreach($dmsActiveDealers as $dealer) {
            $approvals = $qbApprovalRepository->getPoInvoiceApprovals($dealer->dealer_id);
            $preference = Preference::where('dealer_id', $dealer->dealer_id)->first();
            
            if (!empty($preference)) {
                foreach($approvals as $approval) {
                    $qbObj = json_decode($approval->qb_obj, true);
                    $customField = $qbObj['CustomField'] ?? [];

                    if (!empty($customField) && count($customField) > 0) {
                        $qbObj['CustomField'][0]['DefinitionId'] = $preference->po_num_ref_id;
                        QuickbookApproval::find($approval->id)->update(['qb_obj' => json_encode($qbObj)]);
                    }
                }
            }
        }
    }
}
