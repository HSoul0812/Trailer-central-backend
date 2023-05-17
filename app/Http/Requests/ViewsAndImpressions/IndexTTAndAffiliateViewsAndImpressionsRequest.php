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
            'page' => [
                'integer',
                'min:1',
            ],
            'per_page' => [
                'integer',
                'min:1',
                'max:' . GetTTAndAffiliateViewsAndImpressionCriteria::MAX_PER_PAGE,
            ],
        ];
    }
}
