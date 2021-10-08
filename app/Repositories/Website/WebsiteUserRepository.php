<?php

namespace App\Repositories\Website;

use App\Models\Website\User\WebsiteUser;
use App\Exceptions\NotImplementedException;
use App\Models\Website\User\WebsiteUserToken;

/**
 * Class WebsiteRepository
 * @package App\Repositories\Website
 */
class WebsiteUserRepository implements WebsiteUserRepositoryInterface {

    private $userModel;
    private $tokenModel;

    public function __construct(WebsiteUser $websiteUser, WebsiteUserToken $websiteUserToken) {
        $this->userModel = $websiteUser;
        $this->tokenModel = $websiteUserToken;
    }
    /**
     * @param $params
     * @return WebsiteUser
     */
    public function create($params): WebsiteUser {
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
     * @return WebsiteUser|null
     * @throws NotImplementedException
     */
    public function get($params)
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
