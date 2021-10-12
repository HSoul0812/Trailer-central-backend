<?php


namespace App\Services\User;

use App\Repositories\User\UserRepositoryInterface;
use App\Models\User\NewUser;
use App\Models\User\NewDealerUser;
use App\Models\User\DealerUser;

class UserService
{
    
    private const CRM_USER_LOGIN_ROUTE = 'user/login?e=';
    
    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;
    
    /**   
     * @var NewUser 
     */
    protected $newUser;
    
    /**
     * @var NewDealerUser 
     */
    protected $newDealerUser;

    public function __construct(UserRepositoryInterface $userRepository, NewUser $newUser, NewDealerUser $newDealerUser)
    {
        $this->userRepository = $userRepository;
        $this->newUser = $newUser;
        $this->newDealerUser = $newDealerUser;
    }

    public function setAdminPasswd($dealerId, $passwd)
    {
        return $this->userRepository->setAdminPasswd($dealerId, $passwd);
    }
    
    public function getUserCrmLoginUrl(int $dealerId, ?DealerUser $secondaryUser = null)
    {
        if ($secondaryUser) {
            
            if (empty($secondaryUser->newDealerUser)) {
                $secondaryUserEmail = $secondaryUser->email;
                $secondaryUserPassword = $secondaryUser->password;
            } else {
                $secondaryUserEmail = $secondaryUser->newDealerUser->newUser->email;
                $secondaryUserPassword = $secondaryUser->newDealerUser->newUser->password;
            }
            
            $credentials['email'] = $secondaryUserEmail;
            $credentials['password'] = $secondaryUserPassword;
            $credentials['is_sales_person'] = true;
            $credentials['sales_person_email'] = $secondaryUser->email;
            $credentials['secondary_id'] = $secondaryUser->getAuthIdentifier();
        } else {
            $newDealerUser = $this->newDealerUser->where('id', $dealerId)->first();
        
            if (empty($newDealerUser)) {
                return '';
            }

            $newUser = $newDealerUser->newUser;

            $credentials = [
                'email' => $newUser->email,
                'password' => $newUser->password
            ];
        }
        
        return self::CRM_USER_LOGIN_ROUTE.urlencode(base64_encode(json_encode($credentials)));
    }
}
