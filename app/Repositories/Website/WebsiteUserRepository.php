<?php

namespace App\Repositories\Website;

use App\Models\Website\DealerWebsiteUser;
use App\Exceptions\NotImplementedException;
use App\Models\Website\DealerWebsiteUserToken;

/**
 * Class WebsiteRepository
 * @package App\Repositories\Website
 */
class WebsiteUserRepository implements WebsiteUserRepositoryInterface {

    private $userModel;
    private $tokenModel;

    public function __construct(DealerWebsiteUser $dealerWebsiteUser, DealerWebsiteUserToken $dealerWebsiteUserToken) {
        $this->userModel = $dealerWebsiteUser;
        $this->tokenModel = $dealerWebsiteUserToken;
    }
    /**
     * @param $params
     * @return DealerWebsiteUser
     */
    public function create($params): DealerWebsiteUser {
        $user = $this->userModel->create($params);
        $user->token()->create([
            'access_token' => $params['token']
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
     * @return DealerWebsiteUser
     * @throws NotImplementedException
     */
    public function get($params): DealerWebsiteUser
    {
        $query = $this->userModel->select('*');
        if($params['website_id']) {
            $query->where('website_id', $params['website_id']);
        }
        if($params['email']) {
            $query->where('email', $params['email']);
        }
        return $query->first();
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
