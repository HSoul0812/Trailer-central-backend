<?php

namespace App\Services\User;

use App\Helpers\StringHelper;
use App\Models\User\NewDealerUser;
use App\Models\User\NewUser;
use App\Models\User\User;
use App\Repositories\CRM\User\CrmUserRepositoryInterface;
use App\Repositories\CRM\User\CrmUserRoleRepositoryInterface;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Repositories\User\DealerPartRepositoryInterface;
use App\Repositories\User\NewDealerUserRepositoryInterface;
use App\Repositories\User\NewUserRepositoryInterface;
use App\Repositories\User\UserRepositoryInterface;
use App\Exceptions\Nova\Actions\Dealer\EcommerceActivationException;
use App\Exceptions\Nova\Actions\Dealer\EcommerceDeactivationException;
use App\Repositories\Repository;
use App\Repositories\Website\Config\WebsiteConfigRepositoryInterface;
use App\Repositories\Website\EntityRepositoryInterface as WebsiteEntityRepositoryInterface;
use App\Repositories\Website\WebsiteRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Class DealerOptionsService
 * @package App\Services\User
 */
class DealerOptionsService implements DealerOptionsServiceInterface
{
    private const ECOMMERCE_KEY_ENABLE = "parts/ecommerce/enabled";

    private const TEXTRAIL_PARTS_ENTITY_TYPE = '51';

    private const INACTIVE = 0;

    private const ARCHIVED_ON = 1;

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
     * @var WebsiteRepositoryInterface
     */
    private $websiteRepository;


    /**
     * @var WebsiteEntityRepositoryInterface
     */
    private $websiteEntityRepository;

    /**
     * @var inventoryRepositoryInterface
     */
    private $inventoryRepository;

    /**
     * DealerOptionsService constructor.
     * @param UserRepositoryInterface $userRepository
     * @param CrmUserRepositoryInterface $crmUserRepository
     * @param CrmUserRoleRepositoryInterface $crmUserRoleRepository
     * @param WebsiteConfigRepositoryInterface $websiteConfigRepository
     * @param DealerPartRepositoryInterface $dealerPartRepository
     * @param NewDealerUserRepositoryInterface $newDealerUserRepository
     * @param NewUserRepositoryInterface $newUserRepository
     * @param StringHelper $stringHelper
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param WebsiteEntityRepositoryInterface $websiteEntityRepository
     * @param InventoryRepositoryInterface $inventoryRepository
     */
    public function __construct(
        UserRepositoryInterface          $userRepository,
        CrmUserRepositoryInterface       $crmUserRepository,
        CrmUserRoleRepositoryInterface   $crmUserRoleRepository,
        WebsiteConfigRepositoryInterface $websiteConfigRepository,
        DealerPartRepositoryInterface    $dealerPartRepository,
        NewDealerUserRepositoryInterface $newDealerUserRepository,
        NewUserRepositoryInterface       $newUserRepository,
        StringHelper                     $stringHelper,
        WebsiteRepositoryInterface       $websiteRepository,
        WebsiteEntityRepositoryInterface $websiteEntityRepository,
        InventoryRepositoryInterface     $inventoryRepository
    ) {
        $this->userRepository = $userRepository;
        $this->crmUserRepository = $crmUserRepository;
        $this->crmUserRoleRepository = $crmUserRoleRepository;
        $this->dealerPartRepository = $dealerPartRepository;
        $this->newDealerUserRepository = $newDealerUserRepository;
        $this->newUserRepository = $newUserRepository;

        $this->stringHelper = $stringHelper;
        $this->websiteRepository = $websiteRepository;
        $this->websiteConfigRepository = $websiteConfigRepository;
        $this->websiteEntityRepository = $websiteEntityRepository;

        $this->inventoryRepository = $inventoryRepository;
    }

    /**
     * @param int $dealerId
     * @return bool
     */
    public function deactivateDealer(int $dealerId): bool {
        try {

            $inventoryParams = [
                'active' => self::INACTIVE,
                'is_archived' => self::ARCHIVED_ON,
                'archived_at' => Carbon::now()->format('Y-m-d H:i:s')
            ];

            $this->userRepository->deactivateDealer($dealerId);
            $this->inventoryRepository->archiveInventory($dealerId, $inventoryParams);

            return true;
        } catch (\Exception $e) {
            Log::error("Dealer deactivation error. dealer_id - {$dealerId}", $e->getTrace());

            return false;
        }
    }

    /**
     * @param int $dealerId
     * @return bool
     */
    public function activateDealerClassifieds(int $dealerId): bool
    {
        try {
            $this->userRepository->activateDealerClassifieds($dealerId);

            return true;
        } catch (\Exception $e) {
            Log::error("DealerClassifieds activation error. dealer_id - {$dealerId}", $e->getTrace());

            return false;
        }
    }

    /**
     * @param int $dealerId
     * @return bool
     */
    public function deactivateDealerClassifieds(int $dealerId): bool
    {
        try {
            $this->userRepository->deactivateDealerClassifieds($dealerId);

            return true;
        } catch (\Exception $e) {
            Log::error("DealerClassifieds deactivation error. dealer_id - {$dealerId}", $e->getTrace());

            return false;
        }
    }

    /**
     * @param int $dealerId
     * @return bool
     */
    public function activateDms(int $dealerId): bool
    {
        try {
            $this->userRepository->activateDms($dealerId);

            return true;
        } catch (\Exception $e) {
            Log::error("DMS activation error. dealer_id - {$dealerId}", $e->getTrace());

            return false;
        }
    }

    /**
     * @param int $dealerId
     * @return bool
     */
    public function deactivateDms(int $dealerId): bool
    {
        try {
            $this->userRepository->deactivateDms($dealerId);

            return true;
        } catch (\Exception $e) {
            Log::error("DMS deactivation error. dealer_id - {$dealerId}", $e->getTrace());

            return false;
        }
    }

    /**
     * @param int $dealerId
     * @param string $sourceId
     * @return bool
     */
    public function activateCdk(int $dealerId, string $sourceId): bool
    {
        try {
            $this->userRepository->activateCdk($dealerId, $sourceId);

            return true;
        } catch (\Exception $e) {
            Log::error("E-Leads activation error. dealer_id - {$dealerId}", $e->getTrace());

            return false;
        }
    }

    /**
     * @param int $dealerId
     * @return bool
     */
    public function deactivateCdk(int $dealerId): bool
    {
        try {
            $this->userRepository->deactivateCdk($dealerId);

            return true;
        } catch (\Exception $e) {
            Log::error("E-Leads deactivation error. dealer_id - {$dealerId}", $e->getTrace());

            return false;
        }
    }


    /**
     * @param int $dealerId
     * @return bool
     */
    public function activateMarketing(int $dealerId): bool
    {
        try {
            $this->userRepository->activateMarketing($dealerId);

            return true;
        } catch (\Exception $e) {
            Log::error("Marketing activation error. dealer_id - {$dealerId}", $e->getTrace());

            return false;
        }
    }

    /**
     * @param int $dealerId
     * @return bool
     */
    public function deactivateMarketing(int $dealerId): bool
    {
        try {
            $this->userRepository->deactivateMarketing($dealerId);

            return true;
        } catch (\Exception $e) {
            Log::error("Marketing deactivation error. dealer_id - {$dealerId}", $e->getTrace());

            return false;
        }
    }

    /**
     * @param int $dealerId
     * @return bool
     */
    public function activateMobile(int $dealerId): bool
    {
        try {
            $this->userRepository->activateMobile($dealerId);

            return true;
        } catch (\Exception $e) {
            Log::error("Mobile activation error. dealer_id - {$dealerId}", $e->getTrace());

            return false;
        }
    }

    /**
     * @param int $dealerId
     * @return bool
     */
    public function deactivateMobile(int $dealerId): bool
    {
        try {
            $this->userRepository->deactivateMobile($dealerId);

            return true;
        } catch (\Exception $e) {
            Log::error("Mobile deactivation error. dealer_id - {$dealerId}", $e->getTrace());

            return false;
        }
    }


    /**
     * @param int $dealerId
     * @return bool
     */
    public function activateScheduler(int $dealerId): bool
    {
        try {
            $this->userRepository->activateScheduler($dealerId);

            return true;
        } catch (\Exception $e) {
            Log::error("Scheduler activation error. dealer_id - {$dealerId}", $e->getTrace());

            return false;
        }
    }

    /**
     * @param int $dealerId
     * @return bool
     */
    public function deactivateScheduler(int $dealerId): bool
    {
        try {
            $this->userRepository->deactivateScheduler($dealerId);

            return true;
        } catch (\Exception $e) {
            Log::error("Scheduler deactivation error. dealer_id - {$dealerId}", $e->getTrace());

            return false;
        }
    }

    /**
     * @param int $dealerId
     * @return bool
     */
    public function activateELeads(int $dealerId): bool
    {
        try {
            $this->userRepository->activateELeads($dealerId);

            return true;
        } catch (\Exception $e) {
            Log::error("E-Leads activation error. dealer_id - {$dealerId}", $e->getTrace());

            return false;
        }
    }

    /**
     * @param int $dealerId
     * @return bool
     */
    public function deactivateELeads(int $dealerId): bool
    {
        try {
            $this->userRepository->deactivateELeads($dealerId);

            return true;
        } catch (\Exception $e) {
            Log::error("E-Leads deactivation error. dealer_id - {$dealerId}", $e->getTrace());

            return false;
        }
    }

    /**
     * @param int $dealerId
     * @return bool
     */
    public function activateAuction123(int $dealerId): bool
    {
        try {
            $this->userRepository->activateAuction123($dealerId);

            return true;
        } catch (\Exception $e) {
            Log::error("Auction123 activation error. dealer_id - {$dealerId}", $e->getTrace());

            return false;
        }
    }

    /**
     * @param int $dealerId
     * @return bool
     */
    public function deactivateAuction123(int $dealerId): bool
    {
        try {
            $this->userRepository->deactivateAuction123($dealerId);

            return true;
        } catch (\Exception $e) {
            Log::error("Auction123 deactivation error. dealer_id - {$dealerId}", $e->getTrace());

            return false;
        }
    }

    /**
     * @param int $dealerId
     * @return bool
     */
    public function activateAutoConx(int $dealerId): bool
    {
        try {
            $this->userRepository->activateAutoConx($dealerId);

            return true;
        } catch (\Exception $e) {
            Log::error("AutoConx activation error. dealer_id - {$dealerId}", $e->getTrace());

            return false;
        }
    }

    /**
     * @param int $dealerId
     * @return bool
     */
    public function deactivateAutoConx(int $dealerId): bool
    {
        try {
            $this->userRepository->deactivateAutoConx($dealerId);

            return true;
        } catch (\Exception $e) {
            Log::error("AutoConx deactivation error. dealer_id - {$dealerId}", $e->getTrace());

            return false;
        }
    }

    /**
     * @param int $dealerId
     * @return bool
     */
    public function activateCarBase(int $dealerId): bool
    {
        try {
            $this->userRepository->activateCarBase($dealerId);

            return true;
        } catch (\Exception $e) {
            Log::error("CarBase activation error. dealer_id - {$dealerId}", $e->getTrace());

            return false;
        }
    }

    /**
     * @param int $dealerId
     * @return bool
     */
    public function deactivateCarBase(int $dealerId): bool
    {
        try {
            $this->userRepository->deactivateCarBase($dealerId);

            return true;
        } catch (\Exception $e) {
            Log::error("CarBase deactivation error. dealer_id - {$dealerId}", $e->getTrace());

            return false;
        }
    }

    /**
     * @param int $dealerId
     * @return bool
     */
    public function activateDP360(int $dealerId): bool
    {
        try {
            $this->userRepository->activateDP360($dealerId);

            return true;
        } catch (\Exception $e) {
            Log::error("DP360 activation error. dealer_id - {$dealerId}", $e->getTrace());

            return false;
        }
    }

    /**
     * @param int $dealerId
     * @return bool
     */
    public function deactivateDP360(int $dealerId): bool
    {
        try {
            $this->userRepository->deactivateDP360($dealerId);

            return true;
        } catch (\Exception $e) {
            Log::error("DP360 deactivation error. dealer_id - {$dealerId}", $e->getTrace());

            return false;
        }
    }

    /**
     * @param int $dealerId
     * @return bool
     */
    public function activateTrailerUSA(int $dealerId): bool
    {
        try {
            $this->userRepository->activateTrailerUSA($dealerId);

            return true;
        } catch (\Exception $e) {
            Log::error("TrailerUSA activation error. dealer_id - {$dealerId}", $e->getTrace());

            return false;
        }
    }

    /**
     * @param int $dealerId
     * @return bool
     */
    public function deactivateTrailerUSA(int $dealerId): bool
    {
        try {
            $this->userRepository->deactivateTrailerUSA($dealerId);

            return true;
        } catch (\Exception $e) {
            Log::error("TrailerUSA deactivation error. dealer_id - {$dealerId}", $e->getTrace());

            return false;
        }
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

            if ($this->isAllowedParts($dealerId)) {

                $this->websiteConfigRepository->create($newWebsiteConfigActiveParams);
            } else {
                $this->activateParts($dealerId);

                $this->websiteConfigRepository->create($newWebsiteConfigActiveParams);
            }

            $this->websiteEntityRepository->update([
                'entity_type' => self::TEXTRAIL_PARTS_ENTITY_TYPE,
                'website_id' => $webiste->id,
                'entity_view' => 'textrail-parts-list',
                'template' => '2columns-left',
                'parent' => 0,
                'title' => 'Parts Direct Shipping',
                'url_path' => 'parts-direct-shipping',
                'meta_keywords' => 'trailer, parts, shipping, order, cart, ship, direct',
                'meta_description' => 'Trailer parts can be added to your cart, ordered, and shipped directly to your door!',
                'url_path_external' => 0,
                'in_nav' => 0,
                'is_active' => 0,
                'deleted' => 0
            ]);

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
     * @throws EcommerceDeactivationException
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

            $this->websiteEntityRepository->update([
                'entity_type' => self::TEXTRAIL_PARTS_ENTITY_TYPE,
                'website_id' => $webiste->id,
                'is_active' => 0,
                'in_nav' => 0
            ]);

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
    public function activateGoogleFeed(int $dealerId): bool
    {
        try {
            $this->userRepository->activateGoogleFeed($dealerId);

            return true;
        } catch (\Exception $e) {
            Log::error("Google Feed activation error. dealer_id - {$dealerId}", $e->getTrace());

            return false;
        }
    }

    /**
     * @param int $dealerId
     * @return bool
     */
    public function deactivateGoogleFeed(int $dealerId): bool
    {
        try {
            $this->userRepository->deactivateGoogleFeed($dealerId);

            return true;
        } catch (\Exception $e) {
            Log::error("Google Feed deactivation error. dealer_id - {$dealerId}", $e->getTrace());

            return false;
        }
    }

    /**
     * @param int $dealerId
     * @return bool
     */
    public function activateParts(int $dealerId): bool
    {
        try {
            $dealerPartsParams = [
                'dealer_id' => $dealerId,
                'since' => Carbon::now()->format('Y-m-d')
            ];

            $this->dealerPartRepository->create($dealerPartsParams);

            return true;
        } catch (\Exception $e) {
            Log::error("Parts activation error. dealer_id - {$dealerId}", $e->getTrace());

            return false;
        }
    }

    /**
     * @param int $dealerId
     * @return bool
     */
    public function deactivateParts(int $dealerId): bool
    {
        try {
            $dealerPartsParams = [
                'dealer_id' => $dealerId
            ];

            $this->dealerPartRepository->delete($dealerPartsParams);

            return true;
        } catch (\Exception $e) {
            Log::error("Parts deactivation error. dealer_id - {$dealerId}", $e->getTrace());

            return false;
        }
    }

    /**
     * @param int $dealerId
     * @return bool
     */
    public function activateQuoteManager(int $dealerId): bool
    {
        try {
            $this->userRepository->activateQuoteManager($dealerId);

            return true;
        } catch (\Exception $e) {
            Log::error("QuoteManager activation error. dealer_id - {$dealerId}", $e->getTrace());

            return false;
        }
    }

    /**
     * @param int $dealerId
     * @return bool
     */
    public function deactivateQuoteManager(int $dealerId): bool
    {
        try {
            $this->userRepository->deactivateQuoteManager($dealerId);

            return true;
        } catch (\Exception $e) {
            Log::error("QuoteManager deactivation error. dealer_id - {$dealerId}", $e->getTrace());

            return false;
        }
    }

    public function activateUserAccounts(int $dealerId): bool {
        try {
            $websites = $this->websiteRepository->getAll([
                Repository::CONDITION_AND_WHERE => [
                    ['dealer_id', '=', $dealerId]
                ],
            ], false);

            foreach ($websites as $website) {
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
    public function isAllowedParts(int $dealerId): bool
    {
      return !is_null($this->dealerPartRepository->get(['dealer_id' => $dealerId]));
    }

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
     * @param int $dealerId
     * @param string $status
     * @return bool
     */
    public function changeStatus(int $dealerId, string $status): bool
    {
        try {
            $this->userRepository->changeStatus($dealerId, $status);

            return true;
        } catch (\Exception $e) {
            Log::error("Change dealer status error. dealer_id - {$dealerId}", $e->getTrace());

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
