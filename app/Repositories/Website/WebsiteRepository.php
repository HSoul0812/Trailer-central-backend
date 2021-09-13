<?php

namespace App\Repositories\Website;

use App\Exceptions\NotImplementedException;
use App\Exceptions\RepositoryInvalidArgumentException;
use App\Models\Website\Website;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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
        return Website::findOrFail($params['id']);
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
     * @return Collection|LengthAwarePaginator
     */
    public function getAll($params, bool $withDefault = true)
    {
        $query = Website::query();

        $query = $query->select('*');

        if ($withDefault) {
            $query->where(self::DEFAULT_GET_PARAMS[self::CONDITION_AND_WHERE]);
        }

        if (isset($params[self::CONDITION_AND_WHERE]) && is_array($params[self::CONDITION_AND_WHERE])) {
            $query->where($params[self::CONDITION_AND_WHERE]);
        }

        if (!empty($params['dealer_id']) && !empty($params['type']) && $params['type'] === Website::WEBSITE_TYPE_CLASSIFIED) {
            $query->where(function ($query) use ($params) {
                $query->where('dealer_id', '=', $params['dealer_id'])
                    ->orWhereNull('dealer_id');
            });
        } elseif (!empty($params['dealer_id'])) {
            $query->where('dealer_id', '=', $params['dealer_id']);
        }

        if (!empty($params['type'])) {
            $query->where('type', '=', $params['type']);
        }

        if (!empty($params['per_page'])) {
            return $query->paginate($params['per_page'])->appends($params);
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
