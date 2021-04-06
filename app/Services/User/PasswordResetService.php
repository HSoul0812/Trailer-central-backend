<?php

namespace App\Services\User;
use App\Repositories\User\UserRepositoryInterface;
use App\Repositories\User\DealerPasswordResetRepositoryInterface;
use App\Services\User\PasswordResetServiceInterface;

class PasswordResetService implements PasswordResetServiceInterface {
    
    /**
     * @var App\Repositories\User\UserRepositoryInterface
     */
    protected $userRepo;
    
    /**
     * @var App\Repositories\User\DealerPasswordResetRepositoryInterface
     */
    protected $passwordResetRepo;
    
    public function __construct(UserRepositoryInterface $userRepo, DealerPasswordResetRepositoryInterface $passwordResetRepo)
    {
        $this->userRepo = $userRepo;
        $this->passwordResetRepo = $passwordResetRepo;
    }
    
    /**
     * {@inheritDoc}
     */
    public function initReset(string $email) : bool
    {
        $dealer = $this->userRepo->getByEmail($email);
        $this->passwordResetRepo->initiatePasswordReset($dealer);        
        return true;
    }
    
    /**
     * {@inheritDoc}
     */
    public function finishReset(string $code, string $password) : bool
    {
        return $this->passwordResetRepo->completePasswordReset($code, $password);
    }
    
}
