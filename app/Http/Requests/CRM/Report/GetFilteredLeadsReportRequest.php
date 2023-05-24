<?php

namespace App\Http\Requests\CRM\Report;

use App\Http\Requests\Request;
use App\Repositories\CRM\Report\ReportRepositoryInterface;

/**
 * Class GetFilteredLeadsReportRequest
 * @package App\Http\Requests\CRM\Report
 */
class GetFilteredLeadsReportRequest extends Request
{
    protected $rules = [
        'dealer_id' => 'required|integer',
        'date_from' => 'required|date',
        'date_to' => 'required|date',
        'sales_people' => 'array',
        'sales_people.*' => 'integer',
        'lead_source' => 'string',
        'lead_status' => 'array',
        'lead_status.*' => 'string'
    ];
}