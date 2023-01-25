<?php

namespace App\Repositories\User;

use App\Repositories\RepositoryAbstract;
use Illuminate\Database\Eloquent\Collection;
use App\Models\User\User;
use App\Models\User\DealerUser;
use App\Services\Common\EncrypterServiceInterface;
use App\Models\User\DealerUserPermission;
use App\Models\User\AuthToken;
use Illuminate\Support\Facades\DB;

class DealerUserRepository extends RepositoryAbstract implements DealerUserRepositoryInterface
{
    /**
     * @var EncrypterServiceInterface
     */
    protected $encrypterService;

    public function __construct(EncrypterServiceInterface $encrypterService)
    {
        $this->encrypterService = $encrypterService;
    }

    public function getByDealer(int $dealerId): Collection
    {
        $dealer = User::findOrFail($dealerId);
        return $dealer->dealerUsers;
    }

    public function getByDealerEmail(string $dealerEmail): ?User
    {
        return User::where('email', '=', $dealerEmail)->first();
    }

    /**
     * $params:
     * [
     *    'dealer_id' => ...,
     *    'name' => ...,
     *    'email' => ...,
     *    'password' => ...,
     *    'user_permissions' => [
     *      [
     *          'feature' => ... PermissionsInterface feature
     *          'permission_level' => ... PermissionsInterface permission levels
     *      ]
     *    ]
     * ]
     *
     * @param array $params
     */
    public function create($params)
    {
        $dealerUser = null;

        if (empty($params['password'])) {
            throw new \Exception('Password cannot be empty.');
        }

        if ($this->dealerUserExists($params['email'], (int)$params['dealer_id'])) {
            throw new \Exception('Secondary User with the email already exists');
        }

        $params['salt'] = uniqid();
        $params['password'] = $this->encrypterService->encryptBySalt($params['password'], $params['salt']);

        DB::transaction(function() use ($params, &$dealerUser) {
            $dealerUser = DealerUser::create($params);
            
            AuthToken::create([
                'user_id' => $dealerUser->dealer_user_id,
                'user_type' => 'dealer_user',
                'access_token' => md5($dealerUser->dealer_user_id.uniqid())
            ]);
            
            foreach($params['user_permissions'] as $permission) {
                DealerUserPermission::create(['dealer_user_id' => $dealerUser->dealer_user_id] + $permission);
            }

        });

        return $dealerUser;
    }

     /**
     * $params:
     * [
     *    'daler_user_id' => ...,
     *    'name' => ...,
     *    'email' => ...,
     *    'password' => ...,
     *    'user_permissions' => [
     *      [
     *          'feature' => ... PermissionsInterface feature
     *          'permission_level' => ... PermissionsInterface permission levels
     *      ]
     *    ]
     * ]
     *
     * @param array $params
     */
    public function update($params)
    {
        $dealerUser = DealerUser::findOrFail($params['dealer_user_id']);

        DB::transaction(function() use ($params, &$dealerUser) {
            if (empty($params['password'])) {
                unset($params['password']);
            } else {
                $params['password'] = $this->encrypterService->encryptBySalt($params['password'], $dealerUser->salt);
            }

            $dealerUser->fill($params);
            $dealerUser->save();

            if (isset($params['user_permissions'])) {
                foreach ($params['user_permissions'] as $permission) {
                    DealerUserPermission::query()
                        ->where('dealer_user_id', $dealerUser->dealer_user_id)
                        ->where('feature', $permission['feature'])
                        ->delete();

                    DealerUserPermission::create(['dealer_user_id' => $dealerUser->dealer_user_id] + $permission);
                }
            }
        });

        return $dealerUser;
    }

    /**
     * $params:
     * [
     *    'dealer_id', => ...,
     *    'users' => [
     *      'dealer_user_id' => ...,
     *      'name' => ...,
     *      'email' => ...,
     *      'password' => ...,
     *      'user_permissions' => [
     *          [
     *              'feature' => ... PermissionsInterface feature
     *              'permission_level' => ... PermissionsInterface permission levels
     *          ]
     *      ]
     *    ]
     * ]
     *
     * @param array $params
     */
    public function updateBulk(array $params) : Collection
    {
        $users = new Collection();
        foreach($params['users'] as $user) {

            if (empty($user['email'])) {
                $this->delete($user);
                continue;
            }

            if ($this->dealerUserExists($user['email'], (int)$params['dealer_id'], (int)$user['dealer_user_id'])) {
                throw new \Exception('Secondary User with the email already exists');
            }

            $users->add($this->update($user));
        }
        return $users;
    }

    public function delete($params)
    {
        $dealerUser = DealerUser::findOrFail($params['dealer_user_id']);
        return $dealerUser->perms()->delete() && $dealerUser->delete();
    }

    private function dealerUserExists(string $email, int $dealerId, int $dealerUserId = null)
    {
        $query = DealerUser::where('email', $email)->where('dealer_id', $dealerId);

        if ($dealerUserId) {
            $query->where('dealer_user_id', '!=', $dealerUserId);
        }

        return $query->exists();
    }

    /**
     * @param array{dealer_user_id: int} $params
     * @return DealerUser
     * @throws \InvalidArgumentException when dealer_id is not provided
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function get($params): DealerUser
    {
        if (empty($params['dealer_user_id'])) {
            throw new \InvalidArgumentException('Dealer User ID is required');
        }

        return DealerUser::findOrFail($params['dealer_user_id']);
    }
}
