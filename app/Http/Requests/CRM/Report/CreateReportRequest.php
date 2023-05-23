<?php

namespace App\Http\Requests\CRM\Report;

use App\Http\Requests\Request;
use App\Models\CRM\Report\Report;
use Illuminate\Validation\Rule;

/**
 * Class CreateReportRequest
 * @package App\Http\Requests\CRM\Report
 */
class CreateReportRequest extends Request
{
    /**
     * @return array
     */
    protected function getRules(): array
    {
        return [
            'user_id' => 'required|integer',
            'report_type' => [
                'required',
                'string',
                Rule::in(Report::REPORT_TYPES)
            ],
            'report_name' => 'required|string',
            'p_start' => 'required|date',
            'p_end' => 'required|date',
            's_start' => 'required|date',
            's_end' => 'required|date',
            'chart_span' => 'required|in:daily,monthly',
            'lead_source' => 'string',
            'sales_people' => 'array',
            'sales_people.*' => 'integer',
            'lead_status' => 'array',
            'lead_status.*' => 'integer',
            'brands' => 'array',
            'brands.*' => 'string',
            'trailer_categories' => 'array',
            'trailer_categories.*' => 'string'
        ];
    }
}