<?php

namespace App\Repositories\User;

use App\Repositories\Repository;

interface UserRepositoryInterface extends Repository {
    
    /**
     * 
     * 
     * @param string $email
     * @param string $password unencrypted password
     */
    public function findUserByEmailAndPassword($email, $password);
    
}
