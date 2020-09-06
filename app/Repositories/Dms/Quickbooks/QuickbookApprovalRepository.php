<?php

namespace App\Repositories\Dms\Quickbooks;

use App\Repositories\Dms\Quickbooks\QuickbookApprovalRepositoryInterface;
use App\Exceptions\NotImplementedException;
use App\Models\CRM\Quickbooks\QuickbookApproval;

/**
 * @author Marcel
 */
class QuickbookApprovalRepository implements QuickbookApprovalRepositoryInterface {

    private $sortOrders = [
        'created_at' => [
            'field' => 'created_at',
            'direction' => 'DESC'
        ],
        '-created_at' => [
            'field' => 'created_at',
            'direction' => 'ASC'
        ],
    ];


    public function create($params) {
        throw new NotImplementedException;
    }

    public function delete($params) {
        throw new NotImplementedException;
    }

    public function get($params) {
        throw new NotImplementedException;
    }

    public function getAll($params) {
        if (isset($params['dealer_id'])) {
            $query = QuickbookApproval::where('dealer_id', '=', $params['dealer_id']);
        } else {
            $query = QuickbookApproval::where('id', '>', 0);  
        }
        if (isset($params['status'])) {
            switch ($params['status']) {
                case QuickbookApproval::TO_SEND:
                    $query = $query->where([
                        ['send_to_quickbook', '=', 0],
                        ['is_approved', '=', 0]
                    ]);
                    break;
                case QuickbookApproval::SENT:
                    $query = $query->where([
                        ['send_to_quickbook', '=', 1],
                        ['is_approved', '=', 1]
                    ]);
                    break;
                case QuickbookApproval::FAILED:
                    $query = $query->where([
                        ['send_to_quickbook', '=', 1],
                        ['is_approved', '=', 0]
                    ]);
                    $query = $query->whereNotNull('error_result');
                    break;
            }
        }
        // In simple mode of quickbook settings, hide qb_items and qb_item_category approvals
        $inSimpleModeQBSetting = true;
        if ($inSimpleModeQBSetting) {
            $query = $query->whereNotIn('tb_name', ['qb_items', 'qb_item_category']);
        }
        if (isset($params['search_term'])) {
            $query = $query->where(function($q) use($params) {
                $q->where('action_type', 'LIKE', '%' . $params['search_term'] . '%')
                    ->orWhere('created_at', 'LIKE', '%' . $params['search_term'] . '%')
                    ->orWhere(function($query) use($params) {
                        $query->filterByTableName($params['search_term']);
                    });
            });
        }
        if (!isset($params['per_page'])) {
            $params['per_page'] = 15;
        }
        if (isset($params['sort'])) {
            $query = $this->addSortQuery($query, $params['sort']);
        }

        return $query->paginate($params['per_page'])->appends($params);
    }

    public function update($params) {
        throw new NotImplementedException;
    }

    private function addSortQuery($query, $sort) {
        if (!isset($this->sortOrders[$sort])) {
            return;
        }
        return $query->orderBy($this->sortOrders[$sort]['field'], $this->sortOrders[$sort]['direction']);
    }

}
