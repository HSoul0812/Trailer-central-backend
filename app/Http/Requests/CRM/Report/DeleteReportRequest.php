<?php

namespace App\Http\Requests\CRM\Report;

use App\Http\Requests\Request;

/**
 * Class DeleteReportRequest
 * @package App\Http\Requests\CRM\Report
 */
class DeleteReportRequest extends Request
{
    protected $rules = [
        'user_id' => 'required|integer',
        'report_id' => 'required|integer'
    ];
}