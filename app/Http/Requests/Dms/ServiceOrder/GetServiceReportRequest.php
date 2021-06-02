<?php

declare(strict_types=1);

namespace App\Http\Requests\Dms\ServiceOrder;

use App\Http\Requests\Request;

class GetServiceReportRequest extends Request
{
    protected function getRules(): array
    {
        return [
            'dealer_id' => 'required|exists:dealer,dealer_id',
            'token' => 'uuid',
            'completed_on_type' => 'string',
            'repair_order_status' => 'string',
            'repair_order_type' => 'string',
            'technician_id' => 'exists:dms_settings_technician,id',
            'from_date' => 'date',
            'to_date' => 'date',
        ];
    }
} 