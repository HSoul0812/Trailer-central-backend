<?php

namespace App\Transformers\Marketing\Craigslist;

use App\Models\Marketing\Craigslist\Account;
use App\Transformers\User\UserTransformer;
use App\Transformers\Marketing\VirtualCardTransformer;
use App\Transformers\Marketing\Craigslist\ProfileTransformer;
use League\Fractal\TransformerAbstract;

/**
 * Class AccountTransformer
 * 
 * @package App\Transformers\Marketing\Craigslist
 */
class AccountTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'dealer',
        'virtualCard',
        'profile'
    ];

    /**
     * @var UserTransformer
     */
    protected $userTransformer;

    /**
     * @var VirtualCardTransformer
     */
    protected $virtualCardTransformer;

    /**
     * @var ProfileTransformer
     */
    protected $profileTransformer;

    public function __construct(
        UserTransformer $userTransformer,
        VirtualCardTransformer $cardTransformer,
        ProfileTransformer $profileTransformer
    ) {
        $this->userTransformer = $userTransformer;
        $this->cardTransformer = $cardTransformer;
        $this->profileTransformer = $profileTransformer;
    }

    /**
     * @param Account $account
     * @return array
     */
    public function transform(Account $account): array
    {
        return [
            'id' => $account->id,
            'dealer_id' => $account->dealer_id,
            'virtual_card_id' => $account->virtual_card_id,
            'profile_id' => $account->profile_id,
            'username' => $account->username,
            'password' => $account->password,
            'smtp_password' => $account->smtp_password,
            'smtp_server' => $account->smtp_server,
            'smtp_port' => $account->smtp_port,
            'smtp_security' => $account->smtp_security,
            'smtp_auth' => $account->smtp_auth,
            'imap_password' => $account->imap_password,
            'imap_server' => $account->imap_server,
            'imap_port' => $account->imap_port,
            'imap_security' => $account->imap_security
        ];
    }

    public function includeDealer(Account $account)
    {
        return $this->collection($account->dealer, $this->userTransformer);
    }

    public function includeVirtualCard(Account $account)
    {
        return $this->collection($account->card, $this->cardTransformer);
    }

    public function includeProfile(Account $account)
    {
        return $this->collection($account->profile, $this->profileTransformer);
    }
}