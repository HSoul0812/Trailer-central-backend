<?php

namespace Tests\Integration\Http\Controllers\User;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User\AuthToken;
use App\Models\User\User;
use App\Models\CRM\User\SalesPerson;
use App\Models\User\DealerLocation;
use App\Models\User\DealerUser;
use App\Models\User\DealerUserPermission;
use App\Models\User\NewUser;
use App\Models\User\Interfaces\PermissionsInterface;
use App\Nova\Permission;

/**
  * Class SecondaryUserControllerTest
  * @package Tests\Integration\Http\Controllers\User\SecondaryUser
  *
  * @coversDefaultClass \App\Http\Controllers\v1\Inventory\SecondaryUsersController
  */
class SecondaryUserControllerTest extends TestCase {
    use WithFaker;

    /** @var User */
    protected $dealer;

    /** @var AuthToken */
    protected $token;

    /** @var SalesPerson */
    protected $salesPerson;

    /** @var DealerLocation */
    protected $location;

    protected $newUser;

    const apiEndpoint = '/api/user/secondary-users';

    public function getAccessToken()
    {
        return $this->token->access_token;
    }

    public function getDealerId()
    {
        return $this->dealer->dealer_id;
    }

    public function getDealerLocationId()
    {
        return $this->location->dealer_location_id;
    }

    public function getSalesPersonId()
    {
        return $this->salesPerson->id;
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->dealer = factory(User::class)->create([
            'type' => User::TYPE_DEALER,
            'state' => User::STATUS_ACTIVE
        ]);

        $this->token = factory(AuthToken::class)->create([
            'user_id' => $this->getDealerId(),
            'user_type' => AuthToken::USER_TYPE_DEALER,
        ]);

        $this->location = factory(DealerLocation::class)->create([
            'dealer_id' => $this->getDealerId()
        ]);

        $this->newUser = factory(NewUser::class)->create([
            'user_id' => $this->dealer->dealer_id
        ]);

        $this->salesPerson = factory(SalesPerson::class)->create([
            'user_id' => $this->newUser->user_id,
            'dealer_location_id' => $this->getDealerLocationId()
        ]);
    }

    /**
     * Transform retrieved response data into form data to be sent back to API
     * @return array
     */
    public function getSecondaryUsersFormData(array $secondaryUsersResponseData)
    {
        $formData = [];
        foreach ($secondaryUsersResponseData['data'] as $user) {
            $userData = [
                'dealer_user_id' => $user['id'],
                'email' => $user['name'],
                'user_permissions' => $user['permissions']['data']
            ];
            $formData[] = $userData;
        }

        return ['users' => $formData];
    }

    /**
     * update specific form data of spesific user before submit to API
     */
    public function updateSecondaryUserFormData(array $formData, int $userId, array $data)
    {
        foreach ($formData['users'] as $index => $user) {

            if ($user['dealer_user_id'] == $userId) {

                foreach ($data as $field => $value) {
                    switch ($field) {
                        case 'email':
                        case 'password':
                        default:
                            $formData['users'][$index][$field] = $value;
                            break;
                        case 'permissions':
                            foreach ($value as $requestedFeature => $requestedPermissionLevel) {
                                foreach ($user['user_permissions'] as $indexPermission => $permission) {
                                    if ($permission['feature'] == $requestedFeature) {

                                        $formData['users'][$index]['user_permissions'][$indexPermission]['permission_level'] = $requestedPermissionLevel;
                                        break;
                                    }
                                }
                            }
                            break;
                    }
                }
                break;
            }
        }

        return $formData;
    }

    public function failedAddSecondaryUserProvider()
    {
        $otherFeatures = array_diff(PermissionsInterface::FEATURES,
            [PermissionsInterface::CRM, PermissionsInterface::LOCATIONS]);

        return [
            'CRM permission with invalid SalesPerson Id' => [
                PermissionsInterface::CRM,
                99999999999999999999999999999999999999999999
            ],
            'CRM permission with invalid string permission' => [
                PermissionsInterface::CRM,
                'invalid_permission_string'
            ],
            'Location permission with invalid Location Id' => [
                PermissionsInterface::LOCATIONS,
                99999999999999999999999999999999999999999999
            ],
            'Location permission with invalid string permission' => [
                PermissionsInterface::LOCATIONS,
                'invalid_permission_string'
            ],
            'Random permission aside from CRM & Location with Id' => [
                $otherFeatures[array_rand($otherFeatures)],
                100
            ],
            'Random permission aside from CRM & Location with invalid string permission' => [
                $otherFeatures[array_rand($otherFeatures)],
                'invalid_permission_string'
            ]
        ];
    }

    /**
     * @dataProvider failedAddSecondaryUserProvider
     *
     * @group DMS
     * @group DMS_SECONDARY_USER
     *
     * @return void
     */
    public function testFailedAddSecondaryUser($feature, $permissionLevel)
    {
        $userData = [
            'email' => $this->faker->email(),
            'password' => $this->faker->password(),
            'user_permissions' => [[
                'feature' => $feature,
                'permission_level' => $permissionLevel
            ]]
        ];

        $this->withHeaders(['access-token' => $this->getAccessToken()])
            ->json('POST', self::apiEndpoint, $userData)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['user_permissions.0.permission_level']);
    }

    public function addSecondayUserProvider()
    {
        $otherFeatures = array_diff(PermissionsInterface::FEATURES,
            [PermissionsInterface::CRM, PermissionsInterface::LOCATIONS, PermissionsInterface::INVENTORY]);
        return [
            [
                'email' => $this->faker->email(),
                'password' => $this->faker->password(),
                'user_permissions' => [
                    [
                        'feature' => PermissionsInterface::CRM,
                        'permission_level' => $this->faker->randomElement(PermissionsInterface::PERMISSION_LEVELS)
                    ],
                    [
                        'feature' => PermissionsInterface::LOCATIONS,
                        'permission_level' => $this->faker->randomElement(PermissionsInterface::PERMISSION_LEVELS)
                    ],
                    [
                        'feature' => PermissionsInterface::INVENTORY,
                        'permission_level' => $this->faker->randomElement(PermissionsInterface::PERMISSION_LEVELS)
                    ],
                    [
                        'feature' => $this->faker->randomElement($otherFeatures),
                        'permission_level' => $this->faker->randomElement(PermissionsInterface::PERMISSION_LEVELS)
                    ]
                ]
            ]
        ];
    }

    /**
     * @group DMS
     * @group DMS_SECONDARY_USER
     *
     * @return int of userId
     */
    public function testAddSecondaryUser()
    {
        list($userData) = $this->addSecondayUserProvider();

        $response = $this->withHeaders(['access-token' => $this->getAccessToken()])
            ->postJson(self::apiEndpoint, $userData)
            ->assertSuccessful()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'created_at',
                    'permissions' => [
                        'data' => [
                            '*' => [
                                'feature',
                                'permission_level'
                            ]
                        ]
                    ]
                ]
            ]);

        $newSecondaryUserId = $response->decodeResponseJson()['data']['id'];

        $this->assertDatabaseHas('dealer_users', [
            'dealer_user_id' => $newSecondaryUserId,
            'email' => $userData['email']
        ]);

        foreach ($userData['user_permissions'] as $userPermission)
            $this->assertDatabaseHas('dealer_user_permissions', [
                'dealer_user_id' => $newSecondaryUserId,
                'feature' => $userPermission['feature'],
                'permission_level' => $userPermission['permission_level']
            ]);
    }

    /**
     * @param int of new userId from previous test
     *
     * @group DMS
     * @group DMS_SECONDARY_USER
     *
     * @return array of the same userId & formData of the API response
     */
    public function testGetSecondaryUser()
    {
        $dealerUser = factory(DealerUser::class)->create([
            'dealer_id' => $this->getDealerId(),
        ]);
        factory(DealerUserPermission::class)->create([
            'dealer_user_id' => $dealerUser->dealer_user_id,
            'feature' => PermissionsInterface::LOCATIONS,
            'permission_level' => PermissionsInterface::CAN_SEE_PERMISSION,
        ]);
        $response = $this->withHeaders(['access-token' => $this->getAccessToken()])
            ->getJson(self::apiEndpoint)
            ->assertSuccessful()
            ->assertJsonCount(1, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'created_at',
                        'permissions' => [
                            'data' => [
                                '*' => [
                                    'feature',
                                    'permission_level'
                                ]
                            ]
                        ]
                    ]
                ]
            ]);
    }

    /**
     * @param array of data from previous test
     *
     * @group DMS
     * @group DMS_SECONDARY_USER
     *
     * @return void
     */
    public function testUpdateSecondaryUser()
    {
        $dealerUser = factory(DealerUser::class)->create([
            'dealer_id' => $this->getDealerId(),
        ]);
        factory(DealerUserPermission::class)->create([
            'dealer_user_id' => $dealerUser->dealer_user_id,
            'feature' => PermissionsInterface::LOCATIONS,
            'permission_level' => PermissionsInterface::CAN_SEE_PERMISSION,
        ]);
        $updatedFormData = [
            'users' => [
                [
                    'dealer_user_id' => $dealerUser->dealer_user_id,
                    'email' => 'test@secondary.user',
                    'password' => 'Test123!',
                    'user_permissions' => [
                        [
                            'permission_level' => PermissionsInterface::CANNOT_SEE_PERMISSION,
                            'feature' => PermissionsInterface::LOCATIONS,
                        ]
                    ]
                ]
            ]
        ];

        $this->withHeaders(['access-token' => $this->getAccessToken()])
            ->putJson(self::apiEndpoint, $updatedFormData)
            ->assertSuccessful()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'created_at',
                        'permissions' => [
                            'data' => [
                                '*' => [
                                    'feature',
                                    'permission_level'
                                ]
                            ]
                        ]
                    ]
                ]
        ])->assertJsonFragment([
            'permission_level' => PermissionsInterface::CANNOT_SEE_PERMISSION
        ]);
    }

    public function failedUpdateSecondaryUserProvider()
    {
        return [
            'CRM permission with invalid SalesPerson Id' => [
                PermissionsInterface::CRM,
                99999999999999999999999999999999999999999999
            ],
            'CRM permission with invalid string permission' => [
                PermissionsInterface::CRM,
                'invalid_permission_string'
            ],
            'Location permission with invalid Location Id' => [
                PermissionsInterface::LOCATIONS,
                99999999999999999999999999999999999999999999
            ],
            'Location permission with invalid string permission' => [
                PermissionsInterface::LOCATIONS,
                'invalid_permission_string'
            ],
            'Random permission aside from CRM & Location with Id' => [
                PermissionsInterface::INVENTORY,
                100
            ],
            'Random permission aside from CRM & Location with invalid string permission' => [
                PermissionsInterface::INVENTORY,
                'invalid_permission_string'
            ]
        ];
    }

    /**
     * @dataProvider failedUpdateSecondaryUserProvider
     * @depends testGetSecondaryUser
     *
     * @group DMS
     * @group DMS_SECONDARY_USER
     *
     * @return void
     */
    public function testFailedUpdateSecondaryUser($feature, $permissionLevel)
    {
        $dealerUser = factory(DealerUser::class)->create([
            'dealer_id' => $this->getDealerId(),
        ]);
        factory(DealerUserPermission::class)->create([
            'dealer_user_id' => $dealerUser->dealer_user_id,
            'feature' => PermissionsInterface::LOCATIONS,
            'permission_level' => PermissionsInterface::CAN_SEE_PERMISSION,
        ]);
        $updatedFormData = [
            'users' => [
                [
                    'dealer_user_id' => $dealerUser->dealer_user_id,
                    'email' => 'test@secondary.user',
                    'password' => 'Test123!',
                    'user_permissions' => [
                        [
                            'permission_level' => $permissionLevel,
                            'feature' => $feature,
                        ]
                    ]
                ]
            ]
        ];
        $this->withHeaders(['access-token' => $this->getAccessToken()])
            ->putJson(self::apiEndpoint, $updatedFormData)
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'users.0.user_permissions.0.permission_level',
            ]);
    }

    /**
     * @param array of data from previous test
     *
     * @group DMS
     * @group DMS_SECONDARY_USER
     *
     * @return void
     */
    public function testUpdateCrmPermissionWithId()
    {
        $dealerUser = factory(DealerUser::class)->create([
            'dealer_id' => $this->getDealerId(),
        ]);
        factory(DealerUserPermission::class)->create([
            'dealer_user_id' => $dealerUser->dealer_user_id,
            'feature' => PermissionsInterface::LOCATIONS,
            'permission_level' => PermissionsInterface::CAN_SEE_PERMISSION,
        ]);
        $updatedFormData = [
            'users' => [
                [
                    'dealer_user_id' => $dealerUser->dealer_user_id,
                    'email' => 'test@secondary.user',
                    'password' => 'Test123!',
                    'user_permissions' => [
                        [
                            'permission_level' => PermissionsInterface::CANNOT_SEE_PERMISSION,
                            'feature' => PermissionsInterface::LOCATIONS,
                        ],
                        [
                            'permission_level' => PermissionsInterface::CAN_SEE_AND_CHANGE_PERMISSION,
                            'feature' => PermissionsInterface::CRM,
                        ]
                    ]
                ]
            ]
        ];

        $this->withHeaders(['access-token' => $this->getAccessToken()])
            ->putJson(self::apiEndpoint, $updatedFormData)
            ->assertSuccessful()
            ->assertJsonCount(1, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'created_at',
                        'permissions' => [
                            'data' => [
                                '*' => [
                                    'feature',
                                    'permission_level'
                                ]
                            ]
                        ]
                    ]
                ]
        ]);
    }

    /**
     * @param array of data from previous test
     *
     * @group DMS
     * @group DMS_SECONDARY_USER
     *
     * @return void
     */
    public function testUpdateLocationPermissionWithId()
    {
        $dealerUser = factory(DealerUser::class)->create([
            'dealer_id' => $this->getDealerId(),
        ]);
        factory(DealerUserPermission::class)->create([
            'dealer_user_id' => $dealerUser->dealer_user_id,
            'feature' => PermissionsInterface::LOCATIONS,
            'permission_level' => PermissionsInterface::CAN_SEE_PERMISSION,
        ]);
        $updatedFormData = [
            'users' => [
                [
                    'dealer_user_id' => $dealerUser->dealer_user_id,
                    'email' => 'test@secondary.user',
                    'password' => 'Test123!',
                    'user_permissions' => [
                        [
                            'permission_level' => PermissionsInterface::CANNOT_SEE_PERMISSION,
                            'feature' => PermissionsInterface::LOCATIONS,
                        ]
                    ]
                ]
            ]
        ];

        $this->withHeaders(['access-token' => $this->getAccessToken()])
            ->putJson(self::apiEndpoint, $updatedFormData)
            ->assertSuccessful(200)
            ->assertJsonMissingValidationErrors()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'created_at',
                        'permissions' => [
                            'data' => [
                                '*' => [
                                    'feature',
                                    'permission_level'
                                ]
                            ]
                        ]
                    ]
                ]
        ]);
    }

    /**
     * @group DMS
     * @group DMS_SECONDARY_USER
     *
     * @return void
     */
    public function testDeleteSecondaryUser()
    {
        $updatedFormData = [
            'users' => []
        ];

        $this->withHeaders(['access-token' => $this->getAccessToken()])
            ->putJson(self::apiEndpoint, $updatedFormData)
            ->assertSuccessful()
            ->assertJsonCount(0, 'data');
    }

    public function tearDown(): void
    {
        $this->salesPerson->delete();

        $this->token->delete();

        $this->location->delete();

        $this->newUser->delete();

        $this->dealer->delete();

        parent::tearDown();
    }
}
