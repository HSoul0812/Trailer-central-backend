<?php

namespace App\Http\Requests\ViewsAndImpressions;

use App\Domains\ViewsAndImpressions\DTOs\GetTTAndAffiliateViewsAndImpressionCriteria;
use App\Http\Requests\IndexRequestInterface;
use App\Http\Requests\Request;
use Illuminate\Validation\Rule;

class IndexTTAndAffiliateViewsAndImpressionsRequest extends Request implements IndexRequestInterface
{
    protected function getRules(): array
    {
        return [
            'search' => 'string',
            'sort_by' => [
                'string',
                Rule::in(GetTTAndAffiliateViewsAndImpressionCriteria::VALID_SORT_BY),
            ],
            'sort_direction' => [
                'string',
                Rule::in(GetTTAndAffiliateViewsAndImpressionCriteria::VALID_SORT_DIRECTION),
            ],
            'page' => 'integer',
            'per_page' => 'integer',
        ];
    }
}
