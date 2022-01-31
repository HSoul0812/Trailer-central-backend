<?php

namespace App\Repositories\User;

use App\Models\User\WebsiteImage;
use App\Models\Website\Website;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Exceptions\NotImplementedException;

class DealerImageRepository implements DealerImageRepositoryInterface
{
    private function getQueryBuilder(array $params): Builder
    {
        $websiteId = Website::whereDealerId($params['dealer_id'])->value('id');

        $query = WebsiteImage::select('*');
        $query = $query->where('website_id', $websiteId);

        return $query;
    }

    /**
     * @param $params
     * @throws NotImplementedException
     */
    public function create($params)
    {
        throw new NotImplementedException;
    }

    /**
     * @param $params
     * @throws NotImplementedException
     */
    public function update($params)
    {
        throw new NotImplementedException;
    }

    /**
     * @param $params
     * @throws NotImplementedException
     */
    public function get($params)
    {
        throw new NotImplementedException;
    }

    /**
     * @param $params
     * @throws NotImplementedException
     */
    public function delete($params)
    {
        throw new NotImplementedException;
    }

    /**
     * Gets all records by $params
     *
     * @param array $params
     */
    public function getAll($params): LengthAwarePaginator
    {
        $query = $this->getQueryBuilder($params);

        if (isset($params['expired'])) {
            if ($params['expired'] == 1) {
                $query->where('expires_at', '<>', null)->where('expires_at', '<', now());
            }

            if ($params['expired'] == 0) {
                $query->where('expires_at', null)->orWhere(function ($subQuery) {
                    $subQuery->where('expires_at', '>', now());
                });
            }
        }

        if (!isset($params['per_page'])) {
            $params['per_page'] = 15;
        }

        return $query->paginate($params['per_page'])->appends($params);
    }
}
