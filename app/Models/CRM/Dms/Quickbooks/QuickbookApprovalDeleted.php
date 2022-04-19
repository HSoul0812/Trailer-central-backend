<?php

namespace App\Models\CRM\Dms\Quickbooks;

use Illuminate\Database\Eloquent\Model;

/**
 * Class QuickbookApproval
 *
 * @package App\Models\CRM\Dms\Quickbooks
 *
 * @property $id
 * @property $dealer_id
 * @property $tb_name
 * @property $tb_primary_id
 * @property $action_type
 * @property $send_to_quickbook
 * @property $qb_obj
 * @property $is_approved
 * @property $sort_order
 * @property $created_at
 * @property $exported_at
 * @property $qb_id
 * @property $error_result
 * @property $removed_by
 * @property $deleted_at
 */
class QuickbookApprovalDeleted extends QuickbookApproval
{
    public const TABLE_NAME = 'quickbook_approval_deleted';

    protected $table = self::TABLE_NAME;

    /**
     * @param QuickbookApproval $obj
     * @param $removed_by
     */
    public function createFromOriginal(QuickbookApproval $obj, $removed_by)
    {
        $this->id = $obj->id;
        $this->dealer_id = $obj->dealer_id;
        $this->action_type = $obj->action_type;
        $this->tb_name = $obj->tb_name;
        $this->tb_primary_id = $obj->tb_primary_id;
        $this->send_to_quickbook = $obj->send_to_quickbook;
        $this->qb_obj = $obj->qb_obj;
        $this->is_approved = $obj->is_approved;
        $this->sort_order = $obj->sort_order;
        $this->created_at = $obj->created_at;
        $this->exported_at = $obj->exported_at;
        $this->qb_id = $obj->qb_id;
        $this->error_result = $obj->error_result;
        $this->deleted_at = new \DateTime();
        $this->removed_by = $removed_by;

        $this->save();
    }
}
