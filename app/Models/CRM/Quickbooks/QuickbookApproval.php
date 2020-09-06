<?php


namespace App\Models\CRM\Quickbooks;


use Illuminate\Database\Eloquent\Model;

class QuickbookApproval extends Model
{
    // Statuses of Quickbook Approvals
    const TO_SEND = 'to_send';
    const SENT = 'sent';
    const FAILED = 'failed';

    protected $table = 'quickbook_approval';

    public $timestamps = false;

}
