<?php
namespace App\Repositories\Website;

use App\Exceptions\NotImplementedException;
use App\Models\Website\User\WebsiteUserFavoriteInventory;
use App\Models\Website\User\WebsiteUserSearchResult;
use App\Utilities\JsonApi\WithRequestQueryable;

class WebsiteUserSearchResultRepository implements WebsiteUserSearchResultRepositoryInterface {
    use WithRequestQueryable;

    private $websiteUserSearchResult;

    public function __construct(WebsiteUserSearchResult $websiteUserSearchResult) {
        $this->websiteUserSearchResult = $websiteUserSearchResult;
    }

    public function create($params)
    {
        return $this->websiteUserSearchResult->firstOrCreate($params);
    }

    public function update($params)
    {
        throw new NotImplementedException();
    }

    public function get($params)
    {
        return $this->websiteUserSearchResult
            ->where('website_user_id', $params['website_user_id'])
            ->where('search_url', $params['search_url'])
            ->first();
    }

    public function delete($params)
    {
        throw new NotImplementedException();
    }

    public function getAll($params)
    {
        $limit = 5;

        if (isset($params['limit'])) {
            $limit = $params['limit'];
        }

        return $this->websiteUserSearchResult
            ->where('website_user_id', $params['website_user_id'])
            ->orderBy('id', 'DESC')
            ->limit($limit)
            ->get();
    }
}
