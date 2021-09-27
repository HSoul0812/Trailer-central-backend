<?php

namespace App\Services\User;

use App\Helpers\StringHelper;
use App\Models\User\NewDealerUser;
use App\Models\User\NewUser;
use App\Models\User\User;
use App\Repositories\CRM\User\CrmUserRepositoryInterface;
use App\Repositories\CRM\User\CrmUserRoleRepositoryInterface;
use App\Repositories\User\DealerPartRepositoryInterface;
use App\Repositories\Website\Config\WebsiteConfigRepositoryInterface;
use App\Repositories\User\NewDealerUserRepositoryInterface;
use App\Repositories\User\NewUserRepositoryInterface;
use App\Repositories\User\UserRepositoryInterface;
use App\Exceptions\Nova\Actions\Dealer\EcommerceActivationException;
use App\Exceptions\Nova\Actions\Dealer\EcommerceDeactivationException;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Class DealerOptionsService
 * @package App\Services\User
 */
class DealerOptionsService implements DealerOptionsServiceInterface
{
    private const ECOMMERCE_KEY_ENABLE = "parts/ecommerce/enabled";
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
     * @var WebsiteConfigRepositoryInterface
     */
    private $websiteConfigRepository;

    /**
     * @var DealerPartRepositoryInterface
     */
    private $dealerPartRepository;

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
     * DealerOptionsService constructor.
     * @param UserRepositoryInterface $userRepository
     * @param CrmUserRepositoryInterface $crmUserRepository
     * @param CrmUserRoleRepositoryInterface $crmUserRoleRepository
     * @param WebsiteRepositoryInterface $websiteConfigRepository
     * @param DealerPartRepositoryInterface $dealerPartRepositoryInterface
     * @param NewDealerUserRepositoryInterface $newDealerUserRepository
     * @param NewUserRepositoryInterface $newUserRepository
     * @param StringHelper $stringHelper
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        CrmUserRepositoryInterface $crmUserRepository,
        CrmUserRoleRepositoryInterface $crmUserRoleRepository,
        WebsiteConfigRepositoryInterface $websiteConfigRepository,
        DealerPartRepositoryInterface $dealerPartRepository,
        NewDealerUserRepositoryInterface $newDealerUserRepository,
        NewUserRepositoryInterface $newUserRepository,
        StringHelper $stringHelper
    ) {
        $this->userRepository = $userRepository;
        $this->crmUserRepository = $crmUserRepository;
        $this->crmUserRoleRepository = $crmUserRoleRepository;
        $this->websiteConfigRepository = $websiteConfigRepository;
        $this->dealerPartRepository = $dealerPartRepository;
        $this->newDealerUserRepository = $newDealerUserRepository;
        $this->newUserRepository = $newUserRepository;

        $this->stringHelper = $stringHelper;
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
    public function activateECommerce(int $dealerId): bool
    {
      try {
          $user = $this->userRepository->get(['dealer_id' => $dealerId]);
          $webiste = $user->website;
          
          $websiteConfigParams = [
              'website_id' => $webiste->id,
              'key' => self::ECOMMERCE_KEY_ENABLE
          ];

          $websiteConfigall = $this->websiteConfigRepository->getall($websiteConfigParams);
          
          foreach ($websiteConfigall as $key => $websiteConfig) {
            
            $this->websiteConfigRepository->delete(['id' => $websiteConfig->id]);
            
          } 

          $newWebsiteConfigActiveParams = [
            'website_id' => $webiste->id,
            'key' => self::ECOMMERCE_KEY_ENABLE,
            'value' => 1
          ];

          if($this->isAllowedParts($dealerId)) {
                
            $this->websiteConfigRepository->create($newWebsiteConfigActiveParams);
          } else {
            $this->activateParts($dealerId);
            
            $this->websiteConfigRepository->create($newWebsiteConfigActiveParams);
          }
          
          Log::info('E-Commerce has been successfully deactivated', ['user_id' => $user->user_id]);

          return true;
      } catch (\Exception $e) {
          Log::error("E-Commerce activation error. dealer_id - {$dealerId}", $e->getTrace());

          throw new EcommerceActivationException;
      }
    }

    /**
     * @param int $dealerId
     * @return bool
     */
    public function deactivateECommerce(int $dealerId): bool
    {
      try {
          $user = $this->userRepository->get(['dealer_id' => $dealerId]);
          $webiste = $user->website;
          
          $websiteConfigParams = [
              'website_id' => $webiste->id,
              'key' => self::ECOMMERCE_KEY_ENABLE
          ];

          $websiteConfigall = $this->websiteConfigRepository->getall($websiteConfigParams);
          
          foreach ($websiteConfigall as $key => $websiteConfig) {
            
            $this->websiteConfigRepository->delete(['id' => $websiteConfig->id]);
            
          }  

          Log::info('E-Commerce has been successfully deactivated', ['user_id' => $user->user_id]);

          return true;
      } catch (\Exception $e) {
          Log::error("E-Commerce deactivation error. dealer_id - {$user}", $e->getTrace());

          throw new EcommerceDeactivationException;
      }
    }

    /**
     * @param int $dealerId
     * @return bool
     */
    public function activateParts(int $dealerId): bool
    {
      $dealerPartsParams = [
        'dealer_id' => $dealerId,
        'since' => Carbon::now()->format('Y-m-d')
      ];
      $dealerParts = $this->dealerPartRepository->create($dealerPartsParams);
      
      return (bool)$dealerParts;
    }

    /**
     * @param int $dealerId
     * @return bool
     */
    public function isAllowedParts(int $dealerId): bool
    {
      return $this->dealerPartRepository->get(['dealer_id' => $dealerId])->exists();
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
