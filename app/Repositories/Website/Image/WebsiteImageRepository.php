<?php

namespace App\Repositories\Website\Image;

use App\Models\Website\Image\WebsiteImage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Exceptions\NotImplementedException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException;

class WebsiteImageRepository implements WebsiteImageRepositoryInterface
{
    /**
     * @param $params
     * @throws InvalidArgumentException if dealer_id is not set
     * @return Builder
     */
    private function getQueryBuilder(array $params): Builder
    {
        if (!isset($params['website_id'])) {
            throw new InvalidArgumentException("A website id is required");
        }

        $query = WebsiteImage::select('*');
        $query = $query->where('website_id', $params['website_id']);

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
     * Updates a dealer image
     *
     * @param array $params
     * @throws InvalidArgumentException if id is not set
     * @throws ModelNotFoundException if website_image is not found
     * @return WebsiteImage
     */
    public function update($params)
    {
        if (!isset($params['id'])) {
            throw new InvalidArgumentException("Website Image ID is required");
        }

        $image = WebsiteImage::findOrFail($params['id']);
        $image->fill($params)->save();

        return $image;
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

        if (isset($params['expires_at'])) {
            $query->whereDate('expires_at', $params['expires_at']);
        } else if (isset($params['expired'])) {
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