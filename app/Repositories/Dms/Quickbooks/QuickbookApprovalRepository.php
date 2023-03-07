<?php

namespace App\Repositories\Dms\Quickbooks;

use App\Models\CRM\Dms\Quickbooks\QuickbookApprovalDeleted;
use App\Models\CRM\User\Customer;
use Illuminate\Support\Facades\DB;

use App\Exceptions\NotImplementedException;
use App\Models\CRM\Dms\Quickbooks\QuickbookApproval;

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


    public function createForCustomer(Customer $customer)
    {
        $qbInfo = [
            'BillAddr' => [
                'Line1' => $customer->address,
                'City' => $customer->city,
                'Country' => $customer->country,
                'PostalCode' => $customer->postal_code,
                'CountrySubDivisionCode' => $customer->region,
            ],
            'ShipAddr' => [
                'Line1' => $customer->shipping_address,
                'City' => $customer->shipping_city,
                'Country' => $customer->shipping_country,
                'PostalCode' => $customer->shipping_postal_code,
                'CountrySubDivisionCode' => $customer->shipping_region
            ],
            'Mobile' => [
                'FreeFormNumber' => $customer->cell_phone
            ],
            'PrimaryPhone' => [
                'FreeFormNumber' => $customer->work_phone
            ],
            'AlternatePhone' => [
                'FreeFormNumber' => $customer->home_phone
            ],
            'GivenName' => $customer->first_name,
            'MiddleName' => $customer->middle_name,
            'FamilyName' => $customer->last_name,
            'DisplayName' => $customer->display_name,
            'CompanyName' => $customer->company_name,
            'FullyQualifiedName' => $customer->display_name,
            'PrimaryEmailAddr' => [
                'Address' => $customer->email
            ]
        ];

        return $this->create([
            'dealer_id' => $customer->dealer_id,
            'tb_name' => 'dms_customer',
            'tb_primary_id' => $customer->id,
            'qb_info' => $qbInfo,
            'qb_id' => $customer->qb_id,
        ]);
    }

    /**
     * Create an approval object
     *
     * @note code lifted from crm
     * @param $params
     * @return mixed|void
     * @throws \Exception when the `dealer_id` param was not provided
     * @throws \InvalidArgumentException when the `tb_primary_id` param was not provided
     * @throws \InvalidArgumentException when the `tb_name` param was not provided
     * @throws \InvalidArgumentException when the `qb_info` param was not provided
     */
    public function create($params)
    {
        if (empty($params['dealer_id'])) {
            throw new \Exception('Cannot create QB approval object: customer dealer id empty');
        }

        if (empty($params['tb_name'])) {
            throw new \InvalidArgumentException('Cannot create QB approval object: `tb_name` empty');
        }

        if (empty($params['tb_primary_id'])) {
            throw new \InvalidArgumentException('Cannot create QB approval object: `tb_primary_id` empty');
        }

        if (empty($params['qb_info'])) {
            throw new \InvalidArgumentException('Cannot create QB approval object: `qb_info` empty');
        }

        $dealerId = $params['dealer_id'];

        // Remove existing approval object
        $this->deleteByTbPrimaryId($params['tb_primary_id'], $params['tb_name'], $dealerId);

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
            $qbApproval->action_type = QuickbookApproval::ACTION_UPDATE;
            $qbApproval->qb_id = $params['qb_id'];
        }

        $qbApproval->save();

        return $qbApproval;
    }

    /**
     * @param array $params
     * @return QuickbookApproval
     * @throws \Exception when some goes wrong in the data base
     * @throws \InvalidArgumentException when the `id` param was not provided
     */
    public function delete($params): QuickbookApproval
    {
        if (empty($params['id'])) {
            throw new \InvalidArgumentException('The `id` param is required to delete a `QuickbookApproval`');
        }

        $quickBookApproval = QuickbookApproval::find($params['id']);

        if ($quickBookApproval) {
            $qbaDeleted = new QuickbookApprovalDeleted();
            $qbaDeleted->createFromOriginal($quickBookApproval, $params['dealer_id']);

            $quickBookApproval->delete();
        }

        return $quickBookApproval;
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
                        ['is_approved', '=', 0],
                    ]);
                    break;
                case QuickbookApproval::SENT:
                    $query = $query->where([
                        ['send_to_quickbook', '=', 1],
                        ['is_approved', '=', 1],
                    ]);
                    break;
                case QuickbookApproval::FAILED:
                    $query = $query->where([
                        ['send_to_quickbook', '=', 1],
                        ['is_approved', '=', 0],
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
            $search_term = preg_replace('/\s+/', '%',filter_var($params['search_term'], FILTER_SANITIZE_STRING));

            $query = $query->where(function ($q) use ($params, $search_term) {
                $q->where('action_type', 'LIKE', "%$search_term%")
                    ->orWhere('created_at', 'LIKE', "%$search_term%")
                    ->orWhere('qb_obj', 'LIKE', "%TotalAmt%$search_term%Line%") // ticket total
                    ->orWhere('qb_obj', 'LIKE', "%CustomerRef%\"name\"%$search_term%TxnDate%") // customer name
                    ->orWhere('qb_obj', 'LIKE', "%DisplayName%$search_term%PrimaryEmailAddr%") // customer name
                    ->orWhere('qb_obj', 'LIKE', "\{\"Name\"\:\"%$search_term%\"\,\"AccountType%") // customer name
                    ->orWhere('qb_obj', 'LIKE', "%PaymentMethodRef%name%$search_term%") // payment method
                    ->orWhere('qb_obj', 'LIKE', "%PaymentRefNum%$search_term%TotalAmt%") // sales ticket
                    ->orWhere('qb_obj', 'LIKE', "%DocNumber%$search_term%PrivateNote%") // sales ticket
                    ->orWhere(function ($query) use ($params) {
                        $query->filterByTableName($params['search_term']);
                    });

                if (!is_numeric($search_term) && (isset($params['status']) && $params['status'] === QuickbookApproval::TO_SEND)) {
                    $q->orWhere('qb_obj', 'LIKE', "%$search_term%");
                }

                if (isset($params['status']) && $params['status'] === QuickbookApproval::FAILED) {
                    $q->orWhere('qb_obj', 'LIKE', '%' . $params['search_term'] . '%')
                        ->orWhere('error_result', 'LIKE', '%' . $params['search_term'] . '%');
                }
            });
        }

        if (!isset($params['per_page'])) {
            $params['per_page'] = 15;
        }

        if (!isset($params['sort'])) {
            $params['sort'] = 'created_at';
        }
        $query = $this->addSortQuery($query, $params['sort']);

        return $query->paginate($params['per_page'])->appends($params);
    }

    public function update($params) {
        throw new NotImplementedException;
    }

    public function getPoInvoiceApprovals($dealerId) {
        return DB::table('quickbook_approval AS qa')
            ->leftJoin('qb_invoices AS i', 'qa.tb_primary_id', '=', 'i.id')
            ->select('qa.*', 'i.id AS invoice_id')
            ->where('qa.tb_name', '=', 'qb_invoices')
            ->where('qa.dealer_id', '=', $dealerId)
            ->where('is_approved', '=', 0)
            ->whereNotNull('i.po_no')
            ->get();
    }

    private function addSortQuery($query, $sort) {
        if (!isset($this->sortOrders[$sort])) {
            return;
        }
        return $query->orderBy($this->sortOrders[$sort]['field'], $this->sortOrders[$sort]['direction']);
    }

    /**
     * @param int $tbPrimaryId
     * @param string $tableName
     * @param int $dealerId
     * @return bool
     * @throws \Exception
     */
    public function deleteByTbPrimaryId(int $tbPrimaryId, string $tableName, int $dealerId)
    {
        $quickbookApproval = QuickbookApproval::where('tb_primary_id', '=', $tbPrimaryId)
            ->where([
                'tb_name' => $tableName,
                'dealer_id' => $dealerId,
            ])
            ->first();

        if ($quickbookApproval instanceof QuickbookApproval) {
            return $quickbookApproval->delete();
        }

        return false;
    }
}
