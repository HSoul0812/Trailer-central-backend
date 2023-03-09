<?php

namespace App\Services\User;

use App\Models\User\UserAuthenticatable;
use App\Repositories\User\DealerUserRepositoryInterface;
use App\Repositories\User\UserRepositoryInterface;
use App\Repositories\User\DealerPasswordResetRepositoryInterface;

class PasswordResetService implements PasswordResetServiceInterface {

    /**
     * @var \App\Repositories\User\UserRepositoryInterface
     */
    protected $userRepo;

    /**
     * @var \App\Repositories\User\DealerPasswordResetRepositoryInterface
     */
    protected $passwordResetRepo;

    /** @var DealerUserRepositoryInterface */
    private $dealerUserRepo;

    public function __construct(
        UserRepositoryInterface $userRepo,
        DealerUserRepositoryInterface $dealerUserRepo,
        DealerPasswordResetRepositoryInterface $passwordResetRepo
    )
    {
        $this->userRepo = $userRepo;
        $this->dealerUserRepo = $dealerUserRepo;
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

    public function updatePassword(UserAuthenticatable $user, string $password, string $current_password) : bool
    {
        if($user->type === UserAuthenticatable::TYPE_DEALER){
            $user = $this->userRepo->get(['dealer_id' => $user->id]);

            $this->passwordResetRepo->updateDealerPassword($user, $password, $current_password);

            return true;
        }

        $user = $this->dealerUserRepo->get(['dealer_user_id' => $user->id]);

        $this->passwordResetRepo->updateDealerUserPassword($user, $password, $current_password);

        return true;
    }
}
