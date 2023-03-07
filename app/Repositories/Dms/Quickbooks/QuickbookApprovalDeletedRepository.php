<?php

namespace App\Repositories\Dms\Quickbooks;

use App\Models\CRM\Dms\Quickbooks\QuickbookApprovalDeleted;

use App\Exceptions\NotImplementedException;
use App\Models\CRM\Dms\Quickbooks\QuickbookApproval;
use App\Repositories\RepositoryAbstract;

/**
 * @author Marcel
 */
class QuickbookApprovalDeletedRepository extends RepositoryAbstract implements QuickbookApprovalDeletedRepositoryInterface {

    private $sortOrders = [
        'deleted_at' => [
            'field' => 'quickbook_approval_deleted.deleted_at',
            'direction' => 'DESC'
        ],
        '-deleted_at' => [
            'field' => 'quickbook_approval_deleted.deleted_at',
            'direction' => 'ASC'
        ],
        'action_type' => [
            'field' => 'action_type',
            'direction' => 'DESC'
        ],
        '-action_type' => [
            'field' => 'action_type',
            'direction' => 'ASC'
        ],
        'tb_name' => [
            'field' => 'tb_name',
            'direction' => 'DESC'
        ],
        '-tb_name' => [
            'field' => 'tb_name',
            'direction' => 'ASC'
        ],
    ];


    /**
     * Create an approval object
     *
     * @note code lifted from crm
     * @param $params
     * @return mixed|void
     * @throws \Exception
     */
    public function create($params)
    {
        if (empty($params['dealer_id'])) {
            throw new \Exception('Cannot create QB approval object: customer dealer id empty');
        }
        $dealerId = $params['dealer_id'];

        // Remove existing approval object
        $this->delete([
            'dealer_id' => $dealerId,
            'tb_name' => $params['tb_name'],
            'tb_primary_id' => $params['tb_primary_id']
        ]);

        // not sure what this is for yet; just copied from original
        if (empty($params['qb_id']) && isset($params['qb_info']['Active']) && !$params['qb_info']['Active']) return;

        $qbApproval = new QuickbookApproval();
        $qbApproval->dealer_id = $dealerId;
        $qbApproval->tb_name = $params['tb_name'];
        $qbApproval->qb_obj = json_encode($params['qb_info']);
        $qbApproval->tb_primary_id = $params['tb_primary_id'];
        if (isset($params['sort_order'])) {
            $qbApproval->sort_order = $params['sort_order'];
        }
        if (!empty($params['qb_id'])) {
            $qbApproval->action_type = 'update';
            $qbApproval->qb_id = $params['qb_id'];
        }

        $qbApproval->save();
        return $qbApproval;
    }

    public function delete($params): QuickbookApproval
    {
        $quickBookApprovalDeleted = QuickbookApprovalDeleted::find($params['id']);

        if ($quickBookApprovalDeleted) {
            $qba = new QuickbookApproval();
            $qba->createFromDeleted($quickBookApprovalDeleted);

            $quickBookApprovalDeleted->delete();
        }

        return $qba;
    }

    public function get($params) {
        throw new NotImplementedException;
    }

    public function getAll($params)
    {
        if (isset($params['dealer_id'])) {
            $query = QuickbookApprovalDeleted::where('quickbook_approval_deleted.dealer_id', '=', $params['dealer_id']);
        } else {
            $query = QuickbookApprovalDeleted::where('id', '>', 0);
        }

        $query->join('dealer', 'dealer.dealer_id', '=', 'removed_by')
        ->select('quickbook_approval_deleted.*', 'dealer.name as dealer_name');

        // In simple mode of quickbook settings, hide qb_items and qb_item_category approvals
        $inSimpleModeQBSetting = true;
        if ($inSimpleModeQBSetting) {
            $query = $query->whereNotIn('tb_name', ['qb_items', 'qb_item_category']);
        }

        if (isset($params['search_term'])) {
            $search_term = $params['search_term'];
            $query = $query->where(function ($q) use ($params, $search_term) {
                $q->where('action_type', 'LIKE', "%$search_term%")
                    ->orWhere(QuickbookApprovalDeleted::TABLE_NAME . '.created_at', 'LIKE', "%$search_term%")
                    ->orWhere('qb_obj', 'LIKE', "%TotalAmt%$search_term%Line%") // ticket total
                    ->orWhere('qb_obj', 'LIKE', "%CustomerRef%\"name\"%$search_term%TxnDate%") // customer name
                    ->orWhere('qb_obj', 'LIKE', "%DisplayName%$search_term%PrimaryEmailAddr%") // customer name
                    ->orWhere('qb_obj', 'LIKE', "\{\"Name\"\:\"%$search_term%\"\,\"AccountType%") // customer name
                    ->orWhere('qb_obj', 'LIKE', "%PaymentMethodRef%name%$search_term%") // payment method
                    ->orWhere('qb_obj', 'LIKE', "%PaymentRefNum%$search_term%TotalAmt%") // sales ticket
                    ->orWhere('qb_obj', 'LIKE', "%DocNumber%$search_term%PrivateNote%") // sales ticket
                    ->orWhere(function ($query) use ($search_term) {
                        $query->filterByTableName($search_term);
                    });

                $status = $params['status'] ?? null;
                if (!empty($status)) {
                    if (!is_numeric($search_term) && $status === QuickbookApproval::TO_SEND) {
                        $q->orWhere('qb_obj', 'LIKE', "%$search_term%");
                    }

                    if ($status === QuickbookApproval::FAILED) {
                        $q->orWhere('error_result', 'LIKE', "%$search_term%")
                            ->orWhere('qb_obj', 'LIKE', "%$search_term%");
                    }
                }
            });
        }

        if (!isset($params['per_page'])) {
            $params['per_page'] = 15;
        }

        if (!isset($params['sort'])) {
            $params['sort'] = 'deleted_at';
        }

        $query = $this->addSortQuery($query, $params['sort']);

        return $query->paginate($params['per_page'])->appends($params);
    }


    private function addSortQuery($query, $sort) {
        if (!isset($this->sortOrders[$sort])) {
            return $query;
        }
        return $query->orderBy($this->sortOrders[$sort]['field'], $this->sortOrders[$sort]['direction']);
    }
}
