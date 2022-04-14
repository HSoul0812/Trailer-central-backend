<?php

namespace App\Services\Marketing\Craigslist;

use App\Repositories\Marketing\Craigslist\ProfileRepositoryInterface;
use App\Services\Marketing\Craigslist\DTOs\Account;
use App\Services\Marketing\Craigslist\DTOs\ProfileAccounts;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Class ProfileService
 * 
 * @package App\Services\Marketing\Facebook
 */
class ProfileService implements ProfileServiceInterface
{
    /**
     * @var ProfileRepositoryInterface
     */
    protected $profiles;

    /**
     * Construct Facebook Marketplace Service
     * 
     * @param ProfileRepositoryInterface $profiles
     */
    public function __construct(
        ProfileRepositoryInterface $profiles
    ) {
        $this->profiles = $profiles;

        // Create Marketplace Logger
        $this->log = Log::channel('facebook');
    }

    /**
     * Get Profiles
     * 
     * @param array $params
     * @return ProfileAccounts
     */
    public function profiles(array $params): ProfileAccounts {
        // Get Profiles
        $profiles = $this->profiles->getAll($params);

        // Parse Accounts From Profiles
        $accounts = $this->parseAccounts($profiles);

        // Return Profiles + Accounts
        return new ProfileAccounts([
            'profiles' => $profiles,
            'accounts' => $accounts
        ]);
    }


    /**
     * Parse Accounts From Chosen Profiles
     * 
     * @param Collection $profiles
     * @return Collection<Account>
     */
    private function parseAccounts(Collection $profiles) {
        // Get Accounts
        $accounts = array();
        foreach($profiles as $profile) {
            // Add Username to Accounts Array
            if(!isset($accounts[$profile->username])) {
                $accounts[$profile->username] = [];
            }

            // Append Profiles
            $accounts[$profile->username][] = $profile;
        }

        // Create Accounts
        $response = new Collection();
        foreach($accounts as $username => $profiles) {
            $response->push(new Account(['username' => $username, 'profiles' => $profiles]));
        }

        // Return Response
        return $response;
    }
}
