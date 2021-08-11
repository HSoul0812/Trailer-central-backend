<?php

namespace App\Repositories\Website;

use App\Exceptions\RepositoryInvalidArgumentException;
use App\Models\Website\WebsiteDealerUrl;
use App\Repositories\RepositoryAbstract;

/**
 * Class WebsiteDealerUrlRepository
 * @package App\Repositories\Website
 */
class WebsiteDealerUrlRepository extends RepositoryAbstract implements WebsiteDealerUrlRepositoryInterface
{
    /**
     * @param array $params
     * @return bool
     */
    public function exists(array $params): bool
    {
        if (!isset($params['location_id']) && !isset($params['not_location_id'])) {
            throw new RepositoryInvalidArgumentException('location_id has been missed');
        }

        $query = WebsiteDealerUrl::query();

        if (isset($params['location_id'])) {
            $query->where('location_id', '=', $params['location_id']);
        }

        if (isset($params['not_location_id'])) {
            $query->where('location_id', '!=', $params['not_location_id']);
        }

        if (isset($params['url'])) {
            $query->where('url', '=', $params['url']);
        }

        return $query->exists();
    }

    /**
     * @param array $params
     *
     * @return WebsiteDealerUrl
     */
    public function create($params): WebsiteDealerUrl
    {
        return WebsiteDealerUrl::create($params);
    }

    /**
     * @param array $params
     * @return bool|void
     */
    public function update($params): WebsiteDealerUrl
    {
        if (!isset($params['location_id'])) {
            throw new RepositoryInvalidArgumentException('location_id has been missed');
        }

        $query = WebsiteDealerUrl::query();
        $query->where('location_id', '=', $params['location_id']);

        $websiteDealerUrl = $query->firstOrFail();

        $websiteDealerUrl->fill($params);
        $websiteDealerUrl->save();

        return $websiteDealerUrl;
    }
}
