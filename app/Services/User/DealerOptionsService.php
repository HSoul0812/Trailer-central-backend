<?php

namespace App\Services\User;

use App\Models\CRM\User\UserRole;
use App\Models\User\CrmUser;
use App\Models\User\User;
use App\Repositories\CRM\User\CrmUserRepositoryInterface;
use App\Repositories\CRM\User\CrmUserRoleRepositoryInterface;
use App\Repositories\User\UserRepositoryInterface;
use Illuminate\Support\Facades\Log;

/**
 * Class DealerOptionsService
 * @package App\Services\User
 */
class DealerOptionsService
{
    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var CrmUserRepositoryInterface
     */
    private $crmUserRepository;

    /**
     * @var CrmUserRoleRepositoryInterface
     */
    private $crmUserRoleRepository;

    /**
     * DealerOptionsService constructor.
     * @param UserRepositoryInterface $userRepository
     * @param CrmUserRepositoryInterface $crmUserRepository
     * @param CrmUserRoleRepositoryInterface $crmUserRoleRepository
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        CrmUserRepositoryInterface $crmUserRepository,
        CrmUserRoleRepositoryInterface $crmUserRoleRepository
    ) {
        $this->userRepository = $userRepository;
        $this->crmUserRepository = $crmUserRepository;
        $this->crmUserRoleRepository = $crmUserRoleRepository;
    }

    /**
     * @param int $dealerId
     * @return bool
     */
    public function activateCrm(int $dealerId): bool
    {
        try {
            $this->userRepository->beginTransaction();

            /** @var User $user */
            $user = $this->userRepository->get(['dealer_id' => $dealerId]);
            $crmUser = $user->crmUser;
            $newDealerUser = $user->newDealerUser;

            if ($crmUser instanceof CrmUser) {
                $crmUserParams = [
                    'user_id' => $crmUser->user_id,
                    'active' => 1
                ];

                $this->crmUserRepository->update($crmUserParams);
            } else {
                $crmUserParams = [
                    'user_id' => $newDealerUser->user_id,
                    'logo' => '',
                    'first_name' => '',
                    'last_name' => '',
                    'display_name' => '',
                    'dealer_name' => $user->name,
                    'active' => 1
                ];

                $this->crmUserRepository->create($crmUserParams);
            }

            $crmUserRole = $this->crmUserRoleRepository->get(['user_id' => $newDealerUser->user_id]);

            if (!$crmUserRole instanceof UserRole) {
                $crmUserRoleParams = [
                    'user_id' => $newDealerUser->user_id,
                    'role_id' => 'user'
                ];

                $this->crmUserRoleRepository->create($crmUserRoleParams);
            }

            $this->userRepository->commitTransaction();

            Log::info('CRM has been successfully activated', ['user_id' => $newDealerUser->user_id]);

            return true;
        } catch (\Exception $e) {
            Log::error('CRM activation error.', $e->getTrace());
            $this->userRepository->rollbackTransaction();

            return false;
        }
    }

    /**
     * @param int $dealerId
     * @return bool
     */
    public function deactivateCrm(int $dealerId): bool
    {
        try {
            $user = $this->userRepository->get(['dealer_id' => $dealerId]);
            $newDealerUser = $user->newDealerUser;

            $crmUserParams = [
                'user_id' => $newDealerUser->user_id,
                'active' => false
            ];

            $this->crmUserRepository->update($crmUserParams);

            Log::info('CRM has been successfully deactivated', ['user_id' => $newDealerUser->user_id]);

            return true;
        } catch (\Exception $e) {
            Log::error('CRM deactivation error.', $e->getTrace());

            return false;
        }
    }
}
