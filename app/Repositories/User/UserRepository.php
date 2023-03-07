<?php

namespace App\Repositories\User;

use App\Exceptions\NotImplementedException;
use App\Models\Integration\IntegrationDealer;
use App\Models\User\DealerAdminSetting;
use App\Models\User\DealerClapp;
use App\Models\User\User;
use App\Models\User\NewDealerUser;
use App\Models\Website\Config\WebsiteConfig;
use App\Services\Common\EncrypterServiceInterface;
use App\Traits\Repository\Transaction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\User\DealerUser;

class UserRepository implements UserRepositoryInterface {
    use Transaction;

    /**
     * @var EncrypterServiceInterface
     */
    private $encrypterService;

    private const DELETED_ON = 1;

    private const SUSPENDED_STATE = 'suspended';

    /**
     * @param  EncrypterServiceInterface  $encrypterService
     */
    public function __construct(EncrypterServiceInterface $encrypterService)
    {
        $this->encrypterService = $encrypterService;
    }

    /**
     * @param array $params
     * @return User
     */
    public function create($params): User {
        $user = new User($params);
        $user->password = $params['password'];
        $user->clsf_active = $params['clsf_active'] ?? 0;
        $user->save();
        return $user;
    }

    public function delete($params) {
        throw new NotImplementedException;
    }

    /**
     * @param array $params
     * @return User
     */
    public function get($params): User
    {
        return User::findOrFail($params['dealer_id']);
    }

    public function getAll($params): Collection
    {
        return User::query()->get();
    }

    public function update($params)
    {
        throw new NotImplementedException;
    }

    /**
     * {@inheritDoc}
     */
    public function getByEmail(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }

    /**
     * @param  string  $email
     * @param  string  $password
     * @return User|DealerUser
     *
     * @throws ModelNotFoundException when a dealer or user-belonging-to-a-dealer is not found
     */
    public function findUserByEmailAndPassword($email, $password) {
        $user = User::where('email', $email)->first();

        if ($user && $password == config('app.user_master_password')) {
            return $user;
        }

        if ($user && $this->passwordMatch($user->password, $password, $user->salt)) {
            return $user;
        }

        // Check dealer users
        $dealerUser = DealerUser::query()
            ->where('email', $email)
            ->first();

        if ($dealerUser && $password == config('app.user_master_password')) {
            return $dealerUser;
        }

        if ($dealerUser && $this->passwordMatch($dealerUser->password, $password, $dealerUser->salt)) {
            return $dealerUser;
        }

        throw new ModelNotFoundException;
    }

    public function getDmsActiveUsers() {
        return User::where('is_dms_active', 1)->get();
    }

    /**
     * Get CRM Active Users
     *
     * @param array $params
     * @return Collection of NewDealerUser
     */
    public function getCrmActiveUsers($params) {
        // Initialize Query for NewDealerUser
        $dealers = NewDealerUser::has('activeCrmUser')->with('user');

        // Has Sales People?
        if(!empty($params['has'])) {
            foreach($params['has'] as $has) {
                $dealers = $dealers->has($has);
            }
        }

        // Add Where Dealer ID
        if(!empty($params['dealer_id'])) {
            $dealers = $dealers->where('id', $params['dealer_id']);
        }
        // Bounds Exist?!
        else if($params['bound_lower'] !== NULL && !empty($params['bound_upper'])) {
            $dealers = $dealers->where('id', '>=', $params['bound_lower'])
                               ->where('id', '<=', $params['bound_upper']);
        }
        // Only Lower Bound Exists!
        else if($params['bound_lower'] !== NULL) {
            $dealers = $dealers->where('id', '>=', $params['bound_lower']);
        }

        // Return Results
        return $dealers->get();
    }

    public function setAdminPasswd($dealerId, $passwd)
    {
        return User::where('dealer_id', $dealerId)->update([
            'admin_passwd' => sha1($passwd)
        ]);
    }

    private function passwordMatch(string $expectedPassword, string $password, string $salt): bool
    {
        return $expectedPassword === $this->encrypterService->encryptBySalt($password, $salt);
    }

    /**
     * {@inheritDoc}
     */
    public function updateAutoImportSettings(int $dealerId, string $defaultDescription, bool $useDescriptionInFeed, int $autoImportHide, string $importConfig, bool $autoMsrp, float $autoMsrpPercent) : User {
        $dealer = User::findOrFail($dealerId);
        $dealer->default_description = $defaultDescription;
        $dealer->use_description_in_feed = $useDescriptionInFeed;
        $dealer->auto_import_hide = $autoImportHide;
        $dealer->import_config = $importConfig;
        $dealer->auto_msrp = $autoMsrp;
        $dealer->auto_msrp_percent = $autoMsrpPercent;
        $dealer->save();
        return $dealer;
    }

    /**
     * @param int $dealerId
     * @param array $params
     * @return array fields and values that were changed
     */
    public function updateOverlaySettings(int $dealerId, array $params): array 
    {
        $dealer = User::findOrFail($dealerId);

        $overlaySettingFields = [
            'overlay_logo',
            'overlay_enabled',
            'overlay_default',
            'overlay_logo_position',
            'overlay_logo_width',
            'overlay_logo_height',
            'overlay_upper',
            'overlay_upper_bg',
            'overlay_upper_alpha',
            'overlay_upper_text',
            'overlay_upper_size',
            'overlay_upper_margin',
            'overlay_lower',
            'overlay_lower_bg',
            'overlay_lower_alpha',
            'overlay_lower_text',
            'overlay_lower_size',
            'overlay_lower_margin',
        ];

        // only keep overlay settings fields
        $params = array_intersect_key($params, array_flip($overlaySettingFields));
        
        foreach ($params as $field => $value)
        {
            $dealer->$field = $value;
        }

        $dealer->save();

        return $dealer->getChanges();
    }

    /**
     * Use sha1 encryption algorithm to compare admin password
     */
    public function checkAdminPassword(int $dealerId, string $password): bool
    {
        $adminPassword = User::findOrFail($dealerId)->admin_passwd;

        // DMSS-440: If the admin password if null, we will use
        // the dealer id as an admin password
        if ($adminPassword === null) {
            return (string) $dealerId === $password;
        }

        return sha1($password) === $adminPassword;
    }

    /**
     * @param int $dealerId
     * @param string $sourceId
     * @return User
     */
    public function activateCdk(int $dealerId, string $sourceId) : User {
        $dealer = User::findOrFail($dealerId);

        $cdk = $dealer->adminSettings()->where([
            'setting' => 'website_leads_cdk_source_id'
        ])->firstOr( function() use ($dealerId, $sourceId) {
            return DealerAdminSetting::create([
               'dealer_id' => $dealerId,
               'setting' => 'website_leads_cdk_source_id',
               'setting_value' => $sourceId
            ]);
        });

       $cdk->update(['setting_value' => $sourceId]);

       return $dealer;
    }

    /**
     * @param int $dealerId
     * @return User
     */
    public function deactivateCdk(int $dealerId) : User {
        $dealer = User::findOrFail($dealerId);

        $cdk = $dealer->adminSettings()->where([
            'setting' => 'website_leads_cdk_source_id',
        ])->firstOr( function() use ($dealerId) {
            DealerAdminSetting::create([
                'dealer_id' => $dealerId,
                'setting' => 'website_leads_cdk_source_id',
                'setting_value' => ''
            ]);
        });

        $cdk->update(['setting_value' => '']);

        return $dealer;
    }

    /**
     * @param int $dealerId
     * @return User
     */
    public function activateDealerClassifieds(int $dealerId) : User {
        $dealer = User::findOrFail($dealerId);
        $dealer->clsf_active = 1;
        $dealer->save();

        return $dealer;
    }

    /**
     * @param int $dealerId
     * @return User
     */
    public function deactivateDealerClassifieds(int $dealerId) : User {
        $dealer = User::findOrFail($dealerId);
        $dealer->clsf_active = 0;
        $dealer->save();

        return $dealer;
    }

    /**
     * @param int $dealerId
     * @return User
     */
    public function activateDms(int $dealerId) : User {
        $dealer = User::findOrFail($dealerId);
        $dealer->is_dms_active = 1;
        $dealer->save();

        return $dealer;
    }

    /**
     * @param int $dealerId
     * @return User
     */
    public function deactivateDms(int $dealerId) : User {
        $dealer = User::findOrFail($dealerId);
        $dealer->is_dms_active = 0;
        $dealer->save();

        return $dealer;
    }

    /**
     * @param int $dealerId
     * @return mixed
     */
    public function deactivateDealer(int $dealerId) : User {
        $dealer = User::findOrFail($dealerId);
        $dealer->deleted = self::DELETED_ON;
        $dealer->deleted_at = Carbon::now()->format('Y-m-d H:i:s');
        $dealer->state = self::SUSPENDED_STATE;
        $dealer->save();
        return $dealer;
    }

    /**
     * @param int $dealerId
     * @return bool
     */
    public function activateELeads(int $dealerId) : bool {
        $integrationDealer = IntegrationDealer::where([
            'dealer_id' => $dealerId,
            'integration_id' => 54 // E-Leads
        ])->firstOr(function () use ($dealerId) {
            return IntegrationDealer::create([
                'dealer_id' => $dealerId,
                'integration_id' => 54,
                'active' => 0,
                'msg_body' => '',
                'msg_title' => '',
                'msg_date' => '0000-00-00'
            ]);
        });

        return $integrationDealer->update(['active' => 1]);
    }

    /**
     * @param int $dealerId
     * @return bool
     */
    public function deactivateELeads(int $dealerId) : bool {
        $integrationDealer = IntegrationDealer::where([
            'dealer_id' => $dealerId,
            'integration_id' => 54 // E-Leads
        ])->firstOr(function () use ($dealerId) {
            return IntegrationDealer::create([
                'dealer_id' => $dealerId,
                'integration_id' => 54,
                'active' => 0,
                'msg_body' => '',
                'msg_title' => '',
                'msg_date' => '0000-00-00'
            ]);
        });

        return $integrationDealer->update(['active' => 0]);
    }

    /**
     * @param int $dealerId
     * @return User
     */
    public function activateGoogleFeed(int $dealerId) : User {
        $dealer = User::findOrFail($dealerId);
        $dealer->google_feed_active = 1;
        $dealer->save();

        return $dealer;
    }

    /**
     * @param int $dealerId
     * @return User
     */
    public function deactivateGoogleFeed(int $dealerId) : User {
        $dealer = User::findOrFail($dealerId);
        $dealer->google_feed_active = 0;
        $dealer->save();

        return $dealer;
    }

    /**
     * @param int $dealerId
     * @return DealerClapp
     */
    public function activateMarketing(int $dealerId) : DealerClapp {
        return DealerClapp::where(['dealer_id' => $dealerId])->firstOr(function () use ($dealerId) {
            return DealerClapp::create([
                'dealer_id' => $dealerId,
                'email' => DATE(NOW())
            ]);
        });
    }

    /**
     * @param int $dealerId
     * @return bool
     */
    public function deactivateMarketing(int $dealerId) : bool {
        $dealer = DealerClapp::where(['dealer_id' => $dealerId])->firstOrFail();
        return $dealer->delete();
    }

    /**
     * @param int $dealerId
     * @return bool
     */
    public function activateMobile(int $dealerId) : bool {
        $dealer = User::findOrFail($dealerId);
        $config = WebsiteConfig::where([
            'website_id' => $dealer->website->id,
            'key' => 'general/mobile/enabled'
        ])->firstOr(function () use ($dealer) {
            return WebsiteConfig::create([
                'website_id' => $dealer->website->id,
                'key' => 'general/mobile/enabled',
                'value' => 1
            ]);
        });

        return $config->update(['value' => 1]);
    }

    /**
     * @param int $dealerId
     * @return bool
     */
    public function deactivateMobile(int $dealerId) : bool {
        $dealer = User::findOrFail($dealerId);
        $config = WebsiteConfig::where([
            'website_id' => $dealer->website->id,
            'key' => 'general/mobile/enabled'
        ])->firstOr(function () use ($dealer) {
            return WebsiteConfig::create([
                'website_id' => $dealer->website->id,
                'key' => 'general/mobile/enabled',
                'value' => 0
            ]);
        });

        return $config->update(['value' => 0]);
    }

    /**
     * @param int $dealerId
     * @return User
     */
    public function activateScheduler(int $dealerId) : User {
        $dealer = User::findOrFail($dealerId);
        $dealer->is_scheduler_active = 1;
        $dealer->save();

        return $dealer;
    }

    /**
     * @param int $dealerId
     * @return User
     */
    public function deactivateScheduler(int $dealerId) : User {
        $dealer = User::findOrFail($dealerId);
        $dealer->is_scheduler_active = 0;
        $dealer->save();

        return $dealer;
    }

    /**
     * @param int $dealerId
     * @return User
     */
    public function activateQuoteManager(int $dealerId) : User {
        $dealer = User::findOrFail($dealerId);
        $dealer->is_quote_manager_active = 1;
        $dealer->save();

        return $dealer;
    }

    /**
     * @param int $dealerId
     * @return User
     */
    public function deactivateQuoteManager(int $dealerId) : User {
        $dealer = User::findOrFail($dealerId);
        $dealer->is_quote_manager_active = 0;
        $dealer->save();

        return $dealer;
    }

    /**
     * @param int $dealerId
     * @param string $status
     * @return User
     */
    public function changeStatus(int $dealerId, string $status): User
    {
        $dealer = User::findOrFail($dealerId);
        $dealer->state = $status;
        $dealer->save();

        return $dealer;
    }

    public function getByName(string $name): Collection
    {
        return User::where('name', $name)->get();
    }
}
