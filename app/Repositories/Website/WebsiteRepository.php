<?php

namespace App\Repositories\Website;

use App\Exceptions\NotImplementedException;
use App\Exceptions\RepositoryInvalidArgumentException;
use App\Models\Website\Website;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class WebsiteRepository
 * @package App\Repositories\Website
 */
class WebsiteRepository implements WebsiteRepositoryInterface
{
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
     * @param $params
     * @param bool $withDefault
     * @return Collection
     */
    public function getAll($params, bool $withDefault = true): Collection
    {
        $query = Website::select('*');

        if ($withDefault) {
            $query->where(self::DEFAULT_GET_PARAMS[self::CONDITION_AND_WHERE]);
        }

        if (isset($params[self::CONDITION_AND_WHERE]) && is_array($params[self::CONDITION_AND_WHERE])) {
            $query->where($params[self::CONDITION_AND_WHERE]);
        }

        return $query->get();
    }

    /**
     * @param $params
     * @param bool $withDefault
     * @return Collection
     */
    public function getAllByConfigParams($params, bool $withDefault = true): Collection
    {
        $query = Website::select('*');

        if (!isset($params['config']) || !is_array($params['config'])) {
            throw new RepositoryInvalidArgumentException('Missed config key');
        }

        if ($withDefault) {
            $query->where(self::DEFAULT_GET_PARAMS[self::CONDITION_AND_WHERE]);
        }

        if (isset($params[self::CONDITION_AND_WHERE]) && is_array($params[self::CONDITION_AND_WHERE])) {
            $query->where($params[self::CONDITION_AND_WHERE]);
        }

        $query->whereHas('websiteConfigs', function($query) use ($params) {
            foreach ($params['config'] as $key => $value) {
                $query->where('key', $key);
                $query->where('value', $value);
            }
        });

        return $query->get();
    }
}
