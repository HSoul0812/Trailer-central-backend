<?php

namespace App\Services\User;

use Exception;
use Carbon\Carbon;
use App\Models\User\User;
use App\Models\User\NewUser;
use App\Helpers\StringHelper;
use App\Models\User\DealerClapp;
use App\Models\User\NewDealerUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\User\DealerAdminSetting;
use App\Repositories\GenericRepository;
use App\Models\Integration\IntegrationDealer;
use App\Repositories\User\UserRepositoryInterface;
use App\Repositories\User\NewUserRepositoryInterface;
use App\Repositories\User\DealerPartRepositoryInterface;
use App\Repositories\Website\WebsiteRepositoryInterface;
use App\Repositories\CRM\User\CrmUserRepositoryInterface;
use App\Repositories\User\NewDealerUserRepositoryInterface;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Repositories\CRM\User\CrmUserRoleRepositoryInterface;
use App\Repositories\Marketing\Craigslist\DealerRepositoryInterface;
use App\Repositories\Website\Config\WebsiteConfigRepositoryInterface;
use App\Repositories\Website\EntityRepositoryInterface as WebsiteEntityRepositoryInterface;

/**
 * Class DealerOptionsService
 * @package App\Services\User
 */
class DealerOptionsService implements DealerOptionsServiceInterface
{
    /**
     * @var string
     */
    public const ECOMMERCE_KEY_ENABLE = "parts/ecommerce/enabled";

    /**
     * @var string
     */
    public const TEXTRAIL_PARTS_ENTITY_TYPE = '51';

    /**
     * @var int
     */
    public const INACTIVE = 0;

    /**
     * @var int
     */
    public const ARCHIVED_ON = 1;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var DealerRepositoryInterface
     */
    private $dealerRepository;

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
     * @var array
     */
    public $specialSubscriptions = [
        'crm',
        'cdk',
        'marketing',
        'mobile',
        'ecommerce',
        'parts',
        'user_accounts'
    ];

    /**
     * DealerOptionsService constructor.
     * @param UserRepositoryInterface $userRepository
     * @param DealerRepositoryInterface $dealerRepository
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
        DealerRepositoryInterface        $dealerRepository,
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
        $this->dealerRepository = $dealerRepository;
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
     * {@inheritDoc}
     */
    public function manageDealerSubscription(int $dealerId, object $fields): bool
    {
        try {
            if (in_array($fields->subscription, $this->specialSubscriptions)) {
                switch($fields->subscription) {
                    case 'cdk':
                        return $this->manageCdk($dealerId, $fields->active, $fields->source_id);
                    case 'crm':
                        return $this->manageCrm($dealerId, $fields->active);
                    case 'marketing':
                        return $this->manageMarketing($dealerId, $fields->active);
                    case 'mobile':
                        return $this->manageMobile($dealerId, $fields->active);
                    case 'ecommerce':
                        return $this->manageEcommerce($dealerId, $fields->active);
                    case 'parts':
                        return $this->manageParts($dealerId, $fields->active);
                    case 'user_accounts':
                        return $this->manageUserAccounts($dealerId, $fields->active);
                }
            }

            $data = [
                'dealer_id' => $dealerId,
                $fields->subscription => $fields->active
            ];

            return $this->userRepository->update($data);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * {@inheritDoc}
     */
    public function manageHiddenIntegration(int $dealerId, int $integrationId, bool $active): bool
    {
        try {
            $integrationDealer = IntegrationDealer::where([
                'dealer_id' => $dealerId,
                'integration_id' => $integrationId
            ])->firstOr(function () use ($dealerId, $integrationId, $active) {
                return IntegrationDealer::create([
                    'dealer_id' => $dealerId,
                    'integration_id' => $integrationId,
                    'active' => $active,
                    'msg_body' => '',
                    'msg_title' => '',
                    'msg_date' => '0000-00-00'
                ]);
            });

            return $integrationDealer->update(['active' => $active]);
        } catch (Exception $e) {
            Log::error("Activation error. dealer_id - {$dealerId}", $e->getTrace());

            return false;
        }
    }

    /**
     * Change dealer Active state from active/deleted
     * Also
     * Deactivate: Archive active units
     * Active: Unarchive the archived units from last deactivation date
     *
     * @param int $dealerId
     * @param bool $active
     * @return bool
     * @throws Exception
     */
    public function toggleDealerActiveStatus(int $dealerId, bool $active): bool
    {
        try {
            // Transaction added in case of any exception occurs we don't mess any data
            DB::beginTransaction();
            $deletedAt = User::find($dealerId)->deleted_at;
            $datetime = Carbon::now()->format('Y-m-d H:i:s');

            $inventoryParams = [
                'dealer_id' => $dealerId,
                'active' => $active,
                'is_archived' => $active ? 0 : self::ARCHIVED_ON,
                'archived_at' => $active ? null : $datetime
            ];

            $this->userRepository->manageDealerActiveState($dealerId, $active, $datetime);
            $this->inventoryRepository->massUpdateDealerInventoryOnActiveStateChange($dealerId, $inventoryParams, $deletedAt);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            Log::error("Dealer managing error. dealer_id - {$dealerId}", $e->getTrace());
            DB::rollback();
            throw new Exception($e->getMessage());
        }
    }

    /**
     * {@inheritDoc}
     */
    public function manageCdk(int $dealerId, bool $active, $sourceId = ''): bool
    {
        try {
            $this->userRepository->beginTransaction();

            /** @var User $user */
            $dealer = $this->userRepository->get(['dealer_id' => $dealerId]);

            if ($active && empty($sourceId)) {
                throw new \Exception('Source Id is required when activating CDK.');
            }

            $sourceId = $active ? ($sourceId ?? '') : '';

            $cdk = $dealer->adminSettings()->where([
                'setting' => 'website_leads_cdk_source_id'
            ])->firstOr( function() use ($dealerId, $sourceId) {
                DealerAdminSetting::create([
                    'dealer_id' => $dealerId,
                    'setting' => 'website_leads_cdk_source_id',
                    'setting_value' => ''
                ]);
            });

            $cdk->update(['setting_value' => $sourceId]);
            $this->userRepository->commitTransaction();
            return true;
        } catch (Exception $e) {
            Log::error("CRM activation error. dealer_id - {$dealerId}", $e->getTrace());
            $this->userRepository->rollbackTransaction();
            throw new Exception($e->getMessage());
        }
    }

    /**
     * {@inheritDoc}
     */
    public function manageCrm(int $dealerId, bool $active): bool
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
                    'active' => $active
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
                    'active' => $active
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

            return true;
        } catch (Exception $e) {
            Log::error("CRM activation error. dealer_id - {$dealerId}", $e->getTrace());
            $this->userRepository->rollbackTransaction();
            throw new Exception($e->getMessage());
        }
    }

    /**
     * {@inheritDoc}
     */
    public function manageECommerce(int $dealerId, bool $active): bool
    {
        try {
            $this->userRepository->beginTransaction();

            /** @var User $user */
            $user = $this->userRepository->get(['dealer_id' => $dealerId]);

            if (is_null($user->website)) {
                throw new Exception('There\'s no website associated to this dealer.');
            }

            $webiste = $user->website;

            $websiteConfigParams = [
                'website_id' => $webiste->id,
                'key' => self::ECOMMERCE_KEY_ENABLE
            ];

            $websiteConfigall = $this->websiteConfigRepository->getall($websiteConfigParams);

            foreach ($websiteConfigall as $key => $websiteConfig) {
                $this->websiteConfigRepository->delete(['id' => $websiteConfig->id]);
            }

            if (!$active) {
                $this->websiteEntityRepository->update([
                    'entity_type' => self::TEXTRAIL_PARTS_ENTITY_TYPE,
                    'website_id' => $webiste->id,
                    'is_active' => 0,
                    'in_nav' => 0
                ]);

                $this->userRepository->commitTransaction();
                return true;
            }

            $newWebsiteConfigActiveParams = [
                'website_id' => $webiste->id,
                'key' => self::ECOMMERCE_KEY_ENABLE,
                'value' => 1
            ];

            if (!$this->isAllowedParts($dealerId)) {
                $this->manageParts($dealerId, true);
            }
            $this->websiteConfigRepository->create($newWebsiteConfigActiveParams);

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
                'is_active' => true,
                'deleted' => 0
            ]);

            $this->userRepository->commitTransaction();
            return true;
        } catch (Exception $e) {
            Log::error("E-Commerce activation error. dealer_id - {$dealerId}", $e->getTrace());
            $this->userRepository->rollbackTransaction();
            throw new Exception($e->getMessage());
        }
    }

    /**
     * {@inheritDoc}
     */
    public function manageMarketing(int $dealerId, bool $active): bool
    {
        try {
            $dealer = $this->dealerRepository->get([
                'dealer_id' => $dealerId
            ]);

            if (!$active && !empty($dealer)) {
                $dealer->delete();
            }

            if ($active && empty($dealer)) {
                DealerClapp::create([
                    'dealer_id' => $dealerId
                ]);
            }
            return true;
        } catch (\Exception $e) {
            Log::error("Marketing activation error. dealer_id - {$dealerId}", $e->getTrace());
            throw new Exception($e->getMessage());
        }
    }

    /**
     * {@inheritDoc}
     */
    public function manageMobile(int $dealerId, bool $active): bool
    {
        try {
            $this->userRepository->beginTransaction();

            /** @var User $user */
            $dealer = $this->userRepository->get(['dealer_id' => $dealerId]);

            if (is_null($dealer->website)) {
                throw new \Exception('There\'s no website associated to this dealer.');
            }

            $websiteConfigData = [
                'key' => 'general/mobile/enabled',
                'value' => $active
            ];

            $this->websiteConfigRepository->createOrUpdate($dealer->website->id, $websiteConfigData);
            $this->userRepository->commitTransaction();
            return true;
        } catch (\Exception $e) {
            Log::error("Mobile activation error. dealer_id - {$dealerId}", $e->getTrace());
            $this->userRepository->rollbackTransaction();
            throw new Exception($e->getMessage());
        }
    }

    /**
     * {@inheritDoc}
     */
    public function manageParts(int $dealerId, bool $active): bool
    {
        try {
            $this->dealerPartRepository->beginTransaction();

            $dealerPartsParams = [
                'dealer_id' => $dealerId,
                'since' => Carbon::now()->format('Y-m-d')
            ];

            if (!$active) {
                $this->dealerPartRepository->delete($dealerPartsParams);
            } else {
                $this->dealerPartRepository->create($dealerPartsParams);
            }

            $this->dealerPartRepository->commitTransaction();
            return true;
        } catch (Exception $e) {
            Log::error("Parts activation error. dealer_id - {$dealerId}", $e->getTrace());
            $this->dealerPartRepository->rollbackTransaction();
            throw new Exception($e->getMessage());
        }
    }

    /**
     * {@inheritDoc}
     */
    public function manageUserAccounts(int $dealerId, bool $active): bool
    {
        try {
            $websites = $this->websiteRepository->getAll([
                GenericRepository::CONDITION_AND_WHERE => [
                    ['dealer_id', '=', $dealerId]
                ],
            ], false);

            if (empty($websites)) {
                throw new Exception('There\'s no website associated to this dealer');
            }

            foreach ($websites as $website) {
                $this->websiteConfigRepository->setValue($website->getKey(), 'general/user_accounts', $active);

                if (!$active) {
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
                    return true;
                }

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
        } catch(Exception $e) {
            \Log::error($e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    /**
     * {@inheritDoc}
     */
    public function isAllowedParts(int $dealerId): bool
    {
      return !is_null($this->dealerPartRepository->get(['dealer_id' => $dealerId]));
    }

    /**
     * {@inheritDoc}
     */
    public function changeStatus(int $dealerId, string $status): bool
    {
        try {
            $this->userRepository->beginTransaction();

            if (empty($status)) {
                throw new \Exception('Status value is required to update dealer status.');
            }

            $this->userRepository->changeStatus($dealerId, $status);
            $this->userRepository->commitTransaction();
            return true;
        } catch (\Exception $e) {
            Log::error("Change dealer status error. dealer_id - {$dealerId}", $e->getTrace());
            $this->userRepository->rollbackTransaction();
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @param User $user
     * @return NewDealerUser
     * @throws Exception
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
