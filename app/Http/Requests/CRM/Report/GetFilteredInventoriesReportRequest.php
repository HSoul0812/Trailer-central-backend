<?php

namespace App\Http\Requests\CRM\Report;

use App\Http\Requests\Request;
use App\Repositories\CRM\Report\ReportRepositoryInterface;

/**
 * Class GetFilteredInventoriesReportRequest
 * @package App\Http\Requests\CRM\Report
 */
class GetFilteredInventoriesReportRequest extends Request
{
    protected $rules = [
        'dealer_id' => 'required|integer',
        'date_from' => 'required|date',
        'date_to' => 'required|date',
        'filter_by_pull_type' => 'boolean',
        'object_is_inventory' => 'boolean',
        'brands' => 'array',
        'brands.*' => 'string',
        'categories' => 'array',
        'categories.*' => 'string',
        'lead_source' => 'string'
    ];
}