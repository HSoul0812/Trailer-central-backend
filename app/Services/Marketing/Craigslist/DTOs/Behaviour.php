<?php

namespace App\Services\Marketing\Craigslist\DTOs;

use Illuminate\Support\Collection;
use App\Traits\WithConstructor;
use App\Traits\WithGetter;

/**
 * Class Behaviour
 * 
 * @package App\Services\Marketing\Craigslist\DTOs
 */
class Behaviour
{
    use WithConstructor, WithGetter;

    /**
     * Defines special behaviours for specific uuid's.  Available behaviours:
     * version, slotId, dev, reset-session, blocked
     *
     * @const array
     */
    const UUID_BEHAVIOURS = [
        // Development clients -- reset their session every time / delete redis data caches so we don't need to press the button
        'a000000000000001' => ['dev' => true, 'reset' => true],
        'a000000000000002' => ['dev' => true, 'reset' => true],
        'a000000000000003' => ['dev' => true, 'reset' => true],

        // Blocked
        'a552249025994653' => ['blocked' => true],
        'b802971431795716' => ['blocked' => true],
        'b982493846251353' => ['blocked' => true],
        'cr16421766725059084' => ['blocked' => true]
    ];

    /**
     * Defines special behaviours for specific dealer ID's, including custom
     * internal connection behaviours for automated processes.
     *
     * @const array
     */
    const DEALER_ID_BEHAVIOURS = [
        // Internal Dealer -- For scheduling purposes
        100 => [
            'blocked' => true
        ],
        101 => [
            'profile'  => 0,
            'internal' => true,
            'direct'   => true,
            'slotId'   => 98,
            'dealer'   => 'Direct',
            'type'     => 'direct',
            'email'    => 'direct@trailercentral.com',
            'uuid'     => 'sch0000000000002'
        ],
        102 => [
            'profile'  => 3137,
            'internal' => true,
            'slotId'   => 99,
            'dealer'   => 'Internal',
            'type'     => 'internal',
            'email'    => 'internal@trailercentral.com',
            'uuid'     => 'sch0000000000001',
            'username' => "jconwaycl@gmail.com",
            'password' => "2genieus1!",
            'category' => ['atvs, utvs, snowmobiles - by dealer',
                            'auto parts - by dealer',
                            'cars & trucks - by dealer',
                            'farm & garden - by dealer',
                            'general for sale - by dealer',
                            'heavy equipment - by dealer',
                            'real estate - by broker',
                            'rvs - by dealer',
                            'sporting goods - by dealer',
                            'trailers - by dealer']
        ],
        103 => [
            'profile'  => 3137,
            'internal' => true,
            'edit'     => true,
            'slotId'   => 97,
            'dealer'   => 'Edit',
            'type'     => 'edit',
            'email'    => 'edit@trailercentral.com',
            'uuid'     => 'sch0000000000003',
            'username' => "jconwaycl@gmail.com",
            'password' => "2genieus1!",
            'category' => ['atvs, utvs, snowmobiles - by dealer',
                            'auto parts - by dealer',
                            'cars & trucks - by dealer',
                            'farm & garden - by dealer',
                            'general for sale - by dealer',
                            'heavy equipment - by dealer',
                            'real estate - by broker',
                            'rvs - by dealer',
                            'sporting goods - by dealer',
                            'trailers - by dealer']
        ]
    ];


    /**
     * @var string
     */
    private $version;

    /**
     * @var int
     */
    private $profile;

    /**
     * @var int
     */
    private $dealerId;

    /**
     * @var int
     */
    private $slotId;

    /**
     * @var string
     */
    private $dealer;

    /**
     * @var string
     */
    private $type;


    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $uuid;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @var array<string>
     */
    private $category;


    /**
     * @var bool
     */
    private $dev;

    /**
     * @var bool
     */
    private $blocked;

    /**
     * @var bool
     */
    private $reset;

    /**
     * @var bool
     */
    private $internal;

    /**
     * @var bool
     */
    private $direct;

    /**
     * @var bool
     */
    private $edit;



    /**
     * Get By UUID
     */
    static public function byUuid(string $uuid): Behaviour {
        // Get Behaviour By UUID
        $behaviour = self::UUID_BEHAVIOURS[$uuid];

        // Return Behaviour for UUID
        return new self($behaviour);
    }

    /**
     * Get By Dealer ID
     */
    static public function byDealerId(int $dealerId): Behaviour {
        // Get Behaviour By Dealer ID
        $behaviour = self::DEALER_ID_BEHAVIOURS[$dealerId] ?? [];

        // Add Dealer ID
        $behaviour['dealerId'] = $dealerId;

        // Return Behaviour for Dealer ID
        return new self($behaviour);
    }

    /**
     * Get By Dealer UUID
     */
    static public function byDealerUuid(int $uuid): Behaviour {
        // Initialize Behaviour
        $behaviour = [];

        // Find Dealer Config Override Based on ID Provided
        foreach(self::DEALER_ID_BEHAVIOURS as $dealerId => $config) {
            if($uuid === $config['uuid']) {
                $config['dealerId'] = $dealerId;
                $behaviour = $config;
                break;
            }
        }

        // Return Behaviour for Dealer ID
        return new self($behaviour);
    }

    /**
     * Get By Dealer Email
     */
    static public function byDealerEmail(string $email): Behaviour {
        // Initialize Behaviour
        $behaviour = [];

        // Find Dealer Config Override Based on ID Provided
        foreach(self::DEALER_ID_BEHAVIOURS as $dealerId => $config) {
            if($email === $behaviour['email']) {
                $config['dealerId'] = $dealerId;
                $behaviour = $config;
                break;
            }
        }

        // Return Behaviour for Dealer ID
        return new self($behaviour);
    }

    /**
     * Get Internal Behaviours
     * 
     * @return Collection<Behaviour>
     */
    static public function getAllInternal(): Collection {
        // Find Dealer Config Override Based on ID Provided
        $collection = new Collection();
        foreach(self::DEALER_ID_BEHAVIOURS as $dealerId => $behaviour) {
            if(!empty($behaviour['internal'])) {
                $collection->push(self::byDealerId($dealerId));
            }
        }

        // Return All Internal Behaviours
        return $collection;
    }


    /**
     * Is Internal Client?
     * 
     * @param int $slotId
     * @return bool
     */
    static public function isInternalSlot(int $slotId): bool {
        // Find Dealer Config Override Based on Email Provided
        foreach(self::DEALER_ID_BEHAVIOURS as $behaviour) {
            if(isset($behaviour['slotId']) && (int) $slotId === $behaviour['slotId']) {
                return !empty($behaviour['internal']);
            }
        }

        // Return False
        return false;
    }

    /**
     * Get Internal Login by UUID
     * 
     * @param string $uuid
     * @return array{user: string,
     *               pass: string}
     */
    static public function getInternalLogin(string $uuid): array {
        // Find Dealer Config Override Based on ID Provided
        $behaviour = self::byUuid($uuid);

        // Return Empty Array
        return [
            'user' => $behaviour['username'],
            'pass' => $behaviour['password']
        ];
    }
}