<?php

namespace App\Http\Requests\CRM\Report;

use App\Http\Requests\Request;

/**
 * Class GetReportRequest
 * @package App\Http\Requests\CRM\Report
 */
class GetReportRequest extends Request
{
    protected $rules = [
        'user_id' => 'required|integer',
        'report_type' => 'required|string|in:crm_reports,sales_and_product,trailer_traders'
    ];
}