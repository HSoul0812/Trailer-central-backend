<?php

namespace App\Services\User;

use App\Helpers\StringHelper;
use App\Models\User\NewDealerUser;
use App\Models\User\NewUser;
use App\Models\User\User;
use App\Repositories\CRM\User\CrmUserRepositoryInterface;
use App\Repositories\CRM\User\CrmUserRoleRepositoryInterface;
use App\Repositories\Repository;
use App\Repositories\User\NewDealerUserRepositoryInterface;
use App\Repositories\User\NewUserRepositoryInterface;
use App\Repositories\User\UserRepositoryInterface;
use App\Repositories\Website\Config\WebsiteConfigRepositoryInterface;
use App\Repositories\Website\EntityRepositoryInterface as WebsiteEntityRepositoryInterface;
use App\Repositories\Website\WebsiteRepositoryInterface;
use Illuminate\Support\Facades\Log;

/**
 * Class DealerOptionsService
 * @package App\Services\User
 */
class DealerOptionsService implements DealerOptionsServiceInterface
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
     * @var NewDealerUserRepositoryInterface
     */
    private $newDealerUserRepository;

    /**
     * @var NewUserRepositoryInterface
     */
    private $newUserRepository;

    /**
     * @var StringHelper
     */
    private $stringHelper;

    /**
     * @var WebsiteConfigRepositoryInterface
     */
    private $websiteConfigRepository;

    /**
     * @var WebsiteRepositoryInterface
     */
    private $websiteRepository;


    /**
     * @var WebsiteEntityRepositoryInterface
     */
    private $websiteEntityRepository;

    /**
     * DealerOptionsService constructor.
     * @param UserRepositoryInterface $userRepository
     * @param CrmUserRepositoryInterface $crmUserRepository
     * @param CrmUserRoleRepositoryInterface $crmUserRoleRepository
     * @param NewDealerUserRepositoryInterface $newDealerUserRepository
     * @param NewUserRepositoryInterface $newUserRepository
     * @param StringHelper $stringHelper
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param WebsiteConfigRepositoryInterface $websiteConfigRepository
     * @param WebsiteEntityRepositoryInterface $websiteEntityRepository
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        CrmUserRepositoryInterface $crmUserRepository,
        CrmUserRoleRepositoryInterface $crmUserRoleRepository,
        NewDealerUserRepositoryInterface $newDealerUserRepository,
        NewUserRepositoryInterface $newUserRepository,
        StringHelper $stringHelper,
        WebsiteRepositoryInterface $websiteRepository,
        WebsiteConfigRepositoryInterface  $websiteConfigRepository,
        WebsiteEntityRepositoryInterface $websiteEntityRepository
    ) {
        $this->userRepository = $userRepository;
        $this->crmUserRepository = $crmUserRepository;
        $this->crmUserRoleRepository = $crmUserRoleRepository;
        $this->newDealerUserRepository = $newDealerUserRepository;
        $this->newUserRepository = $newUserRepository;

        $this->stringHelper = $stringHelper;
        $this->websiteRepository = $websiteRepository;
        $this->websiteConfigRepository = $websiteConfigRepository;
        $this->websiteEntityRepository = $websiteEntityRepository;
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

            if (!$newDealerUser instanceof NewDealerUser) {
                $newDealerUser = $this->createNewUser($user);
            }

            if ($crmUser) {
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

            if (!$crmUserRole) {
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
            Log::error("CRM activation error. dealer_id - {$dealerId}", $e->getTrace());
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
            Log::error("CRM deactivation error. dealer_id - {$dealerId}", $e->getTrace());

            return false;
        }
    }

    /**
     * @param int $dealerId
     * @return bool
     */
    public function activateUserAccounts(int $dealerId): bool {
        try {
            $websites = $this->websiteRepository->getAll([
                Repository::CONDITION_AND_WHERE => [
                    ['dealer_id', '=', $dealerId]
                ],
            ], false);

            foreach($websites as $website) {
                $this->websiteConfigRepository->setValue($website->getKey(), 'general/user_accounts', 1);

                $this->websiteEntityRepository->update([
                    'entity_type' => '41',
                    'website_id' => $website->getKey(),
                    'entity_view' => 'login',
                    'template' => '1column',
                    'parent' => 0,
                    'title' => 'Login',
                    'url_path' => 'login',
                    'url_path_external' => 0,
                    'sort_order' => 85,
                    'in_nav' => 1,
                    'is_active' => 1,
                    'deleted' => 0
                ]);
                $this->websiteEntityRepository->update([
                    'entity_type' => '42',
                    'website_id' => $website->getKey(),
                    'entity_view' => 'signup',
                    'template' => '1column',
                    'parent' => 0,
                    'title' => 'SignUp',
                    'url_path' => 'signup',
                    'url_path_external' => 0,
                    'in_nav' => 0,
                    'is_active' => 1,
                    'deleted' => 0
                ]);
                $this->websiteEntityRepository->update([
                    'entity_type' => '43',
                    'website_id' => $website->getKey(),
                    'entity_view' => 'account',
                    'template' => '1column',
                    'parent' => 0,
                    'title' => 'Account Information',
                    'url_path' => 'account',
                    'url_path_external' => 0,
                    'in_nav' => 0,
                    'is_active' => 1,
                    'deleted' => 0
                ]);
                $this->websiteEntityRepository->update([
                    'entity_type' => '44',
                    'website_id' => $website->getKey(),
                    'entity_view' => 'inventory-list-hybrid',
                    'template' => '1column',
                    'parent' => 0,
                    'title' => 'Favorite Inventories',
                    'url_path' => 'favorite-inventories',
                    'url_path_external' => 0,
                    'in_nav' => 0,
                    'is_active' => 1,
                    'deleted' => 0
                ]);
            }

            return true;
        } catch(\Exception $e) {
            \Log::error($e->getMessage());
            return false;
        }
    }

    /**
     * @param int $dealerId
     * @return bool
     */
    public function deactivateUserAccounts(int $dealerId): bool {
        try {
            $websites = $this->websiteRepository->getAll([
                Repository::CONDITION_AND_WHERE => [
                    ['dealer_id', '=', $dealerId]
                ]
            ], false);

            foreach($websites as $website) {
                $this->websiteConfigRepository->setValue($website->getKey(), 'general/user_accounts', 0);

                $this->websiteEntityRepository->delete([
                    'entity_type' => '41',
                    'website_id' => $website->getKey()
                ]);

                $this->websiteEntityRepository->delete([
                    'entity_type' => '42',
                    'website_id' => $website->getKey()
                ]);
                $this->websiteEntityRepository->delete([
                    'entity_type' => '43',
                    'website_id' => $website->getKey()
                ]);
                $this->websiteEntityRepository->delete([
                    'entity_type' => '44',
                    'website_id' => $website->getKey()
                ]);
            }
            return true;
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return false;
        }
    }

    /**
     * @param User $user
     * @return NewDealerUser
     * @throws \Exception
     */
    private function createNewUser(User $user): NewDealerUser
    {
        $newUserParams = [
            'username' => $user->name,
            'email' => $user->email,
            'password' => $this->stringHelper->getRandomHex()
        ];

        /** @var NewUser $newUser */
        $newUser = $this->newUserRepository->create($newUserParams);

        $newDealerUserParams = [
            'user_id' => $newUser->user_id,
            'salt' => $this->stringHelper->getRandomHex(),
            'auto_import_hide' => 0,
            'auto_msrp' => 0
        ];

        /** @var NewDealerUser $newDealerUser */
        $newDealerUser = $this->newDealerUserRepository->create($newDealerUserParams);

        $user->newDealerUser()->save($newDealerUser);

        return $newDealerUser;
    }
}
