<?php

namespace App\Domains\ViewsAndImpressions\DTOs;

use App\Http\Requests\ViewsAndImpressions\IndexTTAndAffiliateViewsAndImpressionsRequest;

class GetTTAndAffiliateViewsAndImpressionCriteria
{
    public const SORT_BY_DEALER_ID = 'dealer_id';

    public const SORT_BY_DEALER_NAME = 'dealer_name';

    public const VALID_SORT_BY = [
        self::SORT_BY_DEALER_ID,
        self::SORT_BY_DEALER_NAME,
    ];

    public const SORT_DIRECTION_ASC = 'asc';

    public const SORT_DIRECTION_DESC = 'desc';

    public const VALID_SORT_DIRECTION = [
        self::SORT_DIRECTION_ASC,
        self::SORT_DIRECTION_DESC,
    ];

    public const DEFAULT_PER_PAGE = 10;

    public ?string $search = null;

    public string $sortBy = self::SORT_BY_DEALER_ID;

    public string $sortDirection = self::SORT_DIRECTION_ASC;

    public int $page = 1;

    public int $perPage = 10;

    public static function fromRequest(IndexTTAndAffiliateViewsAndImpressionsRequest $request): GetTTAndAffiliateViewsAndImpressionCriteria
    {
        $criteria = new GetTTAndAffiliateViewsAndImpressionCriteria();

        $criteria->search = $request->input('search');
        $criteria->sortBy = $request->input('sort_by', self::SORT_BY_DEALER_ID);
        $criteria->sortDirection = $request->input('sort_direction', self::SORT_DIRECTION_ASC);
        $criteria->page = $request->input('page', 1);
        $criteria->perPage = $request->input('per_page', self::DEFAULT_PER_PAGE);

        return $criteria;
    }
}
