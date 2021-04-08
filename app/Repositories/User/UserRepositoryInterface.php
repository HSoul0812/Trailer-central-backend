<?php

namespace App\Repositories\User;

use App\Repositories\Repository;
use App\Models\User\User;

interface UserRepositoryInterface extends Repository {

    /**
     * @param string $email
     * @return App\Models\User\User
     */
    public function getByEmail(string $email) : User;
    /**
     *
     *
     * @param string $email
     * @param string $password unencrypted password
     */
    public function findUserByEmailAndPassword($email, $password);

    /**
     * Returns dealers who have the dms active
     * @return Collection
     */
    public function getDmsActiveUsers();

    /**
     * Get CRM Active Users
     * 
     * @param array $params
     * @return Collection of NewDealerUser
     */
    public function getCrmActiveUsers($params);
    
    public function setAdminPasswd($dealerId, $passwd);

    public function beginTransaction(): void;

    public function commitTransaction(): void;

    public function rollbackTransaction(): void;
}
