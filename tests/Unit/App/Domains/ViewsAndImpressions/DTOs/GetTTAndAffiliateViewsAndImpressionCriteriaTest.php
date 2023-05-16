<?php

namespace Tests\Unit\App\Domains\ViewsAndImpressions\DTOs;

use App\Domains\ViewsAndImpressions\DTOs\GetTTAndAffiliateViewsAndImpressionCriteria;
use App\Http\Requests\ViewsAndImpressions\IndexTTAndAffiliateViewsAndImpressionsRequest;
use Tests\Common\TestCase;

class GetTTAndAffiliateViewsAndImpressionCriteriaTest extends TestCase
{
    public function testItCanInitializeFromEmptyRequestObject()
    {
        $request = new IndexTTAndAffiliateViewsAndImpressionsRequest();

        $criteria = GetTTAndAffiliateViewsAndImpressionCriteria::fromRequest($request);

        $this->assertNull($criteria->search);
        $this->assertEquals(GetTTAndAffiliateViewsAndImpressionCriteria::SORT_BY_DEALER_ID, $criteria->sortBy);
        $this->assertEquals(GetTTAndAffiliateViewsAndImpressionCriteria::SORT_DIRECTION_ASC, $criteria->sortDirection);
        $this->assertEquals(1, $criteria->page);
        $this->assertEquals(GetTTAndAffiliateViewsAndImpressionCriteria::DEFAULT_PER_PAGE, $criteria->perPage);
    }

    public function testItCanInitializeFromProperRequestObject()
    {
        $request = new IndexTTAndAffiliateViewsAndImpressionsRequest([
            'search' => 'search string',
            'sort_by' => GetTTAndAffiliateViewsAndImpressionCriteria::SORT_BY_DEALER_NAME,
            'sort_direction' => GetTTAndAffiliateViewsAndImpressionCriteria::SORT_DIRECTION_DESC,
            'page' => 10,
            'per_page' => 40,
        ]);

        $criteria = GetTTAndAffiliateViewsAndImpressionCriteria::fromRequest($request);

        $this->assertEquals('search string', $criteria->search);
        $this->assertEquals(GetTTAndAffiliateViewsAndImpressionCriteria::SORT_BY_DEALER_NAME, $criteria->sortBy);
        $this->assertEquals(GetTTAndAffiliateViewsAndImpressionCriteria::SORT_DIRECTION_DESC, $criteria->sortDirection);
        $this->assertEquals(10, $criteria->page);
        $this->assertEquals(40, $criteria->perPage);
    }
}
