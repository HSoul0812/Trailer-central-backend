<?php

namespace App\Repositories\User;

use App\Jobs\Inventory\GenerateAllOverlayImagesByDealer;
use App\Models\BatchedJob;
use Carbon\Carbon;
use App\Models\User\User;
use App\Models\User\DealerUser;
use App\Models\User\NewDealerUser;
use App\Models\Inventory\EntityType;
use App\Traits\Repository\Transaction;
use App\Exceptions\NotImplementedException;
use App\Services\Common\EncrypterServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\Builder;

/**
 * class UserRepository
 *
 * @package App\Repositories\User
 */
class UserRepository implements UserRepositoryInterface {
    use Transaction;

    /**
     * @var EncrypterServiceInterface
     */
    private $encrypterService;

    /**
     * @var int
     */
    private const DELETED_ON = 1;

    /**
     * @var string
     */
    private const SUSPENDED_STATE = 'suspended';

    private const PRIVATE_DEALER_IDS = [
        8410,
        1004,
        12213,
        10005,
    ];

    private const FROM_TC = 'trailercentral';

    /**
     * @param  EncrypterServiceInterface  $encrypterService
     */
    public function __construct(EncrypterServiceInterface $encrypterService)
    {
        $this->encrypterService = $encrypterService;
    }

    /**
     * {@inheritDoc}
     */
    public function create($params): User {
        $user = new User($params);
        $user->password = $params['password'];
        $user->clsf_active = $params['clsf_active'] ?? 0;
        $user->save();
        return $user;
    }

    /**
     * {@inheritDoc}
     */
    public function delete($params) {
        throw new NotImplementedException;
    }

    /**
     * {@inheritDoc}
     */
    public function get($params): User
    {
        return User::findOrFail($params['dealer_id']);
    }

    /**
     * {@inheritDoc}
     */
    public function getAll($params): Collection
    {
        return User::query()->get();
    }

    /**
     * {@inheritDoc}
     */
    public function update($params): bool
    {
        $dealer = User::findOrFail($params['dealer_id']);
        return $dealer->update($params);
    }

    public function getEmailSignature(int $dealerId)
    {
        return $this->get(['dealer_id' => $dealerId])->crmUser->email_signature;
    }

    /**
     * {@inheritDoc}
     */
    public function getByEmail(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }

    /**
     * {@inheritDoc}
     */
    public function findUserByEmailAndPassword(string $email, string $password) {
        $user = User::where([
            ['email', '=', $email],
            ['state', '<>','suspended'],
            ['deleted', '=', 0]
        ])->first();

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

    /**
     * {@inheritDoc}
     */
    public function getDmsActiveUsers(): Collection {
        return User::where('is_dms_active', 1)->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getCrmActiveUsers(array $params): Collection {
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

    /**
     * {@inheritDoc}
     */
    public function setAdminPasswd($dealerId, $passwd)
    {
        return User::where('dealer_id', $dealerId)->update([
            'admin_passwd' => sha1($passwd)
        ]);
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
     * {@inheritDoc}
     */
    public function updateOverlaySettings(int $dealerId, array $params): array
    {
        /** @var User $dealer */
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

        $changes = $dealer->getChanges();
        unset($changes['updated_at']);

        if (collect($changes)->except('overlay_enabled')->count() > 0) {
            $dealer->overlay_updated_at = now();
            $dealer->save();
        }

        return $changes;
    }

    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
     */
    public function toggleDealerStatus(int $dealerId, bool $active, $datetime = null): User
    {
        if (is_null($datetime)) {
            $datetime = Carbon::now()->format('Y-m-d H:i:s');
        }

        $dealer = User::findOrFail($dealerId);
        $dealer->deleted = $active ? 0 : self::DELETED_ON;
        $dealer->deleted_at = $active ? null : $datetime;
        $dealer->state = $active ? 'active' : self::SUSPENDED_STATE;
        $dealer->save();
        return $dealer;
    }

    /**
     * {@inheritDoc}
     */
    public function changeStatus(int $dealerId, string $status): User
    {
        $dealer = User::findOrFail($dealerId);
        $dealer->state = $status;
        $dealer->save();

        return $dealer;
    }

    /**
     * {@inheritDoc}
     */
    public function getByName(string $name): Collection
    {
        return User::where('name', $name)->get();
    }

    /**
     * {@inheritDoc}
     */
    public function getTrailerTraderDealers($params)
    {
        $types = array_reduce(EntityType::TRAILER_TRADER_TYPES, function($carry, $item) {
            return array_merge($carry, $item);
        }, []);
        
        if (isset($params['type'])) {
            $types = EntityType::TRAILER_TRADER_TYPES[$params['type']];
        }
        $query = DB::table('dealer')
            ->select([
                'dealer.dealer_id as id',
                'dealer.name',
                'dealer.clsf_active',
                'dealer_location.dealer_location_id',
                'dealer_location.name as location_name',
                'dealer_location.region',
                'dealer_location.city',
                'dealer_location.postalcode',
            ])
            ->join('dealer_location', 'dealer.dealer_id', '=', 'dealer_location.dealer_id')
            ->where('dealer.clsf_active', 1)
            ->whereNotIn('dealer.dealer_id', self::PRIVATE_DEALER_IDS)
            ->where('dealer.from', self::FROM_TC)
            ->when(isset($params['state']), function (Builder $query) use ($params) {
                $query->where('dealer_location.region', $params['state']);
            })
            ->whereExists(function (Builder $query) use ($types) {
                $query
                    ->select(['*'])
                    ->from('inventory')
                    ->whereColumn('inventory.dealer_location_id', 'dealer_location.dealer_location_id')
                    ->where('inventory.is_archived', '!=', 1)
                    ->where('inventory.show_on_website', 1)
                    ->whereIn('inventory.category', $types);
            });
        return $query->get();
    }

    /**
     * @param string $expectedPassword
     * @param string $password
     * @param string $salt
     * @return bool
     */
    private function passwordMatch(string $expectedPassword, string $password, string $salt): bool
    {
        return $expectedPassword === $this->encrypterService->encryptBySalt($password, $salt);
    }

    public function hasRunningOverlayBatch(int $dealerId): bool
    {
        $like = sprintf('%%%s-%d%%', GenerateAllOverlayImagesByDealer::MONITORED_GROUP, $dealerId);

        return BatchedJob::query()
            ->where('batch_id', 'LIKE', $like)
            ->where('total_jobs', '>', 0)
            ->whereNull('finished_at')
            ->exists();
    }
}
