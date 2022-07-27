<?php

namespace App\Repositories\User;

use App\Exceptions\NotImplementedException;
use App\Models\User\User;
use App\Models\User\NewDealerUser;
use App\Services\Common\EncrypterServiceInterface;
use App\Traits\Repository\Transaction;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\User\DealerUser;

class UserRepository implements UserRepositoryInterface {
    use Transaction;

    /**
     * @var EncrypterServiceInterface
     */
    private $encrypterService;

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

    public function getAll($params) {
        throw new NotImplementedException;
    }

    public function update($params) {
        throw new NotImplementedException;
    }

    /**
     * {@inheritDoc}
     */
    public function getByEmail(string $email) : User
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

    public function updateOverlaySettings(int $dealerId, int $overlayEnabled = null, bool $overlay_default = null, string $overlay_logo_position = null, string $overlay_logo_width = null, string $overlay_logo_height = null, string $overlay_upper = null, string $overlay_upper_bg = null, int $overlay_upper_alpha = null, string $overlay_upper_text = null, int $overlay_upper_size = null, int $overlay_upper_margin = null, string $overlay_lower = null, string $overlay_lower_bg = null, int $overlay_lower_alpha = null, string $overlay_lower_text = null, int $overlay_lower_size = null, int $overlay_lower_margin = null, string $overlay_logo_src = null): User {
        $dealer = User::findOrFail($dealerId);
        $dealer->overlay_enabled = $overlayEnabled;
        $dealer->overlay_default = $overlay_default;
        $dealer->overlay_logo_position  = $overlay_logo_position;
        $dealer->overlay_logo_width  = $overlay_logo_width;
        $dealer->overlay_logo_height  = $overlay_logo_height;
        $dealer->overlay_upper = $overlay_upper;
        $dealer->overlay_upper_bg = $overlay_upper_bg;
        $dealer->overlay_upper_alpha = $overlay_upper_alpha;
        $dealer->overlay_upper_text = $overlay_upper_text;
        $dealer->overlay_upper_size = $overlay_upper_size;
        $dealer->overlay_upper_margin = $overlay_upper_margin;
        $dealer->overlay_lower = $overlay_lower;
        $dealer->overlay_lower_bg = $overlay_lower_bg;
        $dealer->overlay_lower_alpha = $overlay_lower_alpha;
        $dealer->overlay_lower_text = $overlay_lower_text;
        $dealer->overlay_lower_size = $overlay_lower_size;
        $dealer->overlay_lower_margin = $overlay_lower_margin;
        if($overlay_logo_src !== null) {
            $dealer->overlay_logo = $overlay_logo_src;
        }
        $dealer->save();
        return $dealer;
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

}
