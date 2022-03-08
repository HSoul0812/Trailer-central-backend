<?php
namespace App\Repositories\Website;

use App\Exceptions\NotImplementedException;
use App\Models\Website\User\WebsiteUserSearchResult;
use App\Utilities\JsonApi\WithRequestQueryable;

class WebsiteUserSearchResultRepository implements WebsiteUserSearchResultRepositoryInterface {
    use WithRequestQueryable;

    private $websiteUserSearchResult;

    public function __construct(WebsiteUserSearchResult $websiteUserSearchResult) {
        $this->websiteUserSearchResult = $websiteUserSearchResult;
    }

    /**
     * @param $params
     * @return WebsiteUserSearchResult
     * @throws \InvalidArgumentException when `website_user_id` is not provided
     */
    public function create($params)
    {
        if (empty($params['website_user_id'])) {
            throw new \InvalidArgumentException("User ID is missing");
        }

        if (empty($params['summary'])) {
            throw new \InvalidArgumentException("Summary is missing");
        }

        if (empty($params['search_url'])) {
            throw new \InvalidArgumentException("Search URL is missing");
        }

        return $this->websiteUserSearchResult->firstOrCreate($params);
    }

    public function update($params)
    {
        throw new NotImplementedException();
    }

    /**
     * @param $params
     * @return WebsiteUserSearchResult|null
     * @throws \InvalidArgumentException
     */
    public function get($params)
    {
        if (empty($params['website_user_id'])) {
            throw new \InvalidArgumentException("User ID is missing");
        }

        return $this->websiteUserSearchResult
            ->where('website_user_id', $params['website_user_id'])
            ->where('search_url', $params['search_url'])
            ->first();
    }

    /**
     * @param $params
     * @return bool
     */
    public function delete($params)
    {
        return WebsiteUserSearchResult::findOrFail($params['search_id'])->delete();
    }

    /**
     * @param $params
     * @return WebsiteUserSearchResult[]
     * @throws \InvalidArgumentException
     */
    public function getAll($params)
    {
        if (empty($params['website_user_id'])) {
            throw new \InvalidArgumentException("User ID is missing");
        }

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