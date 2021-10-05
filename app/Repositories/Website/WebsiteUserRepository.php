<?php

namespace App\Repositories\Website;

use App\Models\Website\DealerWebsiteUser;
use App\Exceptions\NotImplementedException;
use App\Exceptions\RepositoryInvalidArgumentException;
use App\Models\Website\Website;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class WebsiteRepository
 * @package App\Repositories\Website
 */
class WebsiteUserRepository implements WebsiteUserRepositoryInterface {
    public function create($params) {
        $user = new DealerWebsiteUser($params);
        $user->save();
        $user->token()->create([
            'token' => $params['token']
        ]);
        return $user;
    }

    /**
     * @param array $params
     * @return bool|void
     */
    public function update($params)
    {
        throw new NotImplementedException;
    }

    /**
     * @param array $params
     * @return mixed|void
     * @throws NotImplementedException
     */
    public function get($params)
    {
        throw new NotImplementedException;
    }

    /**
     * @param array $params
     * @return bool|void
     * @throws NotImplementedException
     */
    public function delete($params)
    {
        throw new NotImplementedException;
    }

    /**
     * @param array $params
     * @throws NotImplementedException
     */
    public function getAll($params)
    {
        throw new NotImplementedException;
    }
}
