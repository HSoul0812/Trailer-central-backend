<?php

namespace App\Repositories\User;

use App\Repositories\RepositoryAbstract;
use Illuminate\Database\Eloquent\Collection;
use App\Models\User\User;
use App\Models\User\DealerUser;
use App\Services\Common\EncrypterServiceInterface;
use App\Models\User\DealerUserPermission;
use App\Models\User\Interfaces\PermissionsInterface;
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
    
    public function getByDealer(int $dealerId) : Collection
    {
        $dealer = User::findOrFail($dealerId);
        return $dealer->dealerUsers;
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
        
        if ($this->dealerUserExists($params['email'], (int)$params['dealer_id'])) {
            throw new \Exception('Secondary User with the email already exists');
        }
        
        $params['salt'] = uniqid();
        $params['password'] = $this->encrypterService->encryptBySalt($params['password'], $params['salt']);

        DB::transaction(function() use ($params, &$dealerUser) {
            $dealerUser = DealerUser::create($params);
            
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
            $dealerUser->fill($params);
            $dealerUser->save();
            
            DealerUserPermission::where('dealer_user_id', $dealerUser->dealer_user_id)->delete();
            
            foreach($params['user_permissions'] as $permission) {
                DealerUserPermission::create(['dealer_user_id' => $dealerUser->dealer_user_id] + $permission);
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
        return $dealerUser->delete();
    }
    
    private function dealerUserExists(string $email, int $dealerId, int $dealerUserId = null)  
    {
        $query = DealerUser::where('email', $email)->where('dealer_id', $dealerId);
        
        if ($dealerUserId) {
            $query->where('dealer_user_id', '!=', $dealerUserId);
        }
        
        return $query->exists();
    }

}
