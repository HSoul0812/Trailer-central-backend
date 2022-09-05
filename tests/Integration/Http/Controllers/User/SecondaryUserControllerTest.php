<?php

namespace Tests\Integration\Http\Controllers\User;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User\AuthToken;
use App\Models\User\User;
use App\Models\CRM\User\SalesPerson;
use App\Models\User\DealerLocation;
use App\Models\User\NewUser;
use App\Models\User\Interfaces\PermissionsInterface;

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
    public function testAddSecondayUser()
    {
        list($userData) = $this->addSecondayUserProvider();

        $response = $this->withHeaders(['access-token' => $this->getAccessToken()])
            ->json('POST', self::apiEndpoint, $userData) 
            ->assertStatus(200)
            ->assertJsonMissingValidationErrors()
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

        return $newSecondaryUserId;
    }

    /**
     * @depends testAddSecondayUser
     * @param int of new userId from previous test
     *
     * @group DMS
     * @group DMS_SECONDARY_USER
     *
     * @return array of the same userId & formData of the API response
     */
    public function testGetSecondayUser($newSecondaryUserId)
    {
        $this->assertNotNull($newSecondaryUserId);

        $response = $this->withHeaders(['access-token' => $this->getAccessToken()])
            ->json('GET', self::apiEndpoint) 
            ->assertStatus(200)
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

        return [
            'userId' => $newSecondaryUserId,
            'userIndex' => array_search($newSecondaryUserId, array_column($response->decodeResponseJson()['data'], 'id')),
            'formData' => $this->getSecondaryUsersFormData($response->decodeResponseJson())
        ];
    }

    /**
     * @depends testGetSecondayUser
     * @param array of data from previous test
     *
     * @group DMS
     * @group DMS_SECONDARY_USER
     *
     * @return void
     */
    public function testUpdateSecondaryUser($dependedData)
    {
        // update permissions
        $expectedPermissionLevel = $this->faker->randomElement(PermissionsInterface::PERMISSION_LEVELS);
        $updatedFormData = $this->updateSecondaryUserFormData($dependedData['formData'], $dependedData['userId'], [
            'permissions' => [
                PermissionsInterface::CRM => $expectedPermissionLevel,
                PermissionsInterface::LOCATIONS => $expectedPermissionLevel,
                PermissionsInterface::INVENTORY => $expectedPermissionLevel,
            ]
        ]);

        $response = $this->withHeaders(['access-token' => $this->getAccessToken()])
            ->json('PUT', self::apiEndpoint, $updatedFormData) 
            ->assertStatus(200)
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

        $jsonResponse = $response->decodeResponseJson();

        $actualPermissions = $jsonResponse['data'][$dependedData['userIndex']]['permissions']['data'];
        foreach ($actualPermissions as $actualPermission) {
             
            $feature = $actualPermission['feature'];
            $actualPermissionLevel = $actualPermission['permission_level'];

            if (in_array($feature, [PermissionsInterface::CRM, PermissionsInterface::LOCATIONS, PermissionsInterface::INVENTORY])) {
                $this->assertTrue($expectedPermissionLevel === $actualPermissionLevel);
                $this->assertDatabaseHas('dealer_user_permissions', ['dealer_user_id' => $dependedData['userId'], 'feature' => $feature, 'permission_level' => $expectedPermissionLevel]);
            }
        }
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
     * @depends testGetSecondayUser
     *
     * @group DMS
     * @group DMS_SECONDARY_USER
     *
     * @return void
     */
    public function testFailedUpdateSecondaryUser($feature, $permissionLevel, $data)
    {
        $testingFeatures = [PermissionsInterface::CRM, PermissionsInterface::LOCATIONS, PermissionsInterface::INVENTORY];
        $permissionLevelIndex = array_search($feature, $testingFeatures);

        // update permissions
        $updatedFormData = $this->updateSecondaryUserFormData($data['formData'], $data['userId'], [
            'permissions' => [
                $feature => $permissionLevel,
            ]
        ]);

        $this->withHeaders(['access-token' => $this->getAccessToken()])
            ->json('PUT', self::apiEndpoint, $updatedFormData) 
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'users.'. $data['userIndex'] .'.user_permissions.'. $permissionLevelIndex .'.permission_level'
            ]);
    }

    /**
     * @depends testGetSecondayUser
     * @param array of data from previous test
     *
     * @group DMS
     * @group DMS_SECONDARY_USER
     *
     * @return void
     */
    public function testUpdateCrmPermissionWithId($data)
    {
        // update permissions
        $updatedFormData = $this->updateSecondaryUserFormData($data['formData'], $data['userId'], [
            'permissions' => [
                PermissionsInterface::CRM => $this->getSalesPersonId(),
            ]
        ]);

        $this->withHeaders(['access-token' => $this->getAccessToken()])
            ->json('PUT', self::apiEndpoint, $updatedFormData) 
            ->assertStatus(200)
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

        $this->assertDatabaseHas('dealer_user_permissions', [
            'dealer_user_id' => $data['userId'],
            'feature' => PermissionsInterface::CRM,
            'permission_level' => $this->getSalesPersonId()
        ]);
    }

    /**
     * @depends testGetSecondayUser
     * @param array of data from previous test
     *
     * @group DMS
     * @group DMS_SECONDARY_USER
     *
     * @return void
     */
    public function testUpdateLocationPermissionWithId($data)
    {
        // update permissions
        $updatedFormData = $this->updateSecondaryUserFormData($data['formData'], $data['userId'], [
            'permissions' => [
                PermissionsInterface::LOCATIONS => $this->getDealerLocationId(),
            ]
        ]);

        $this->withHeaders(['access-token' => $this->getAccessToken()])
            ->json('PUT', self::apiEndpoint, $updatedFormData) 
            ->assertStatus(200)
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

        $this->assertDatabaseHas('dealer_user_permissions', [
            'dealer_user_id' => $data['userId'],
            'feature' => PermissionsInterface::LOCATIONS,
            'permission_level' => $this->getDealerLocationId()
        ]);
    }

    /**
     * @depends testGetSecondayUser
     * @param array of data from previous test
     *
     * @group DMS
     * @group DMS_SECONDARY_USER
     *
     * @return void
     */
    public function testDeleteSecondaryUser($data)
    {
        // empty out email of the newly created user from the first test
        $updatedFormData = $this->updateSecondaryUserFormData($data['formData'], $data['userId'], ['email' => '']);
        // checking if data exists before deleting
        $this->assertDatabaseHas('dealer_users', ['dealer_user_id' => $data['userId']]);
        $this->assertDatabaseHas('dealer_user_permissions', ['dealer_user_id' => $data['userId']]);

        $this->withHeaders(['access-token' => $this->getAccessToken()])
            ->json('PUT', self::apiEndpoint, $updatedFormData) 
            ->assertStatus(200)
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
            ])
            ->assertJsonMissingExact(['data.*.id', $data['userId']]);

        $this->assertDatabaseMissing('dealer_users', ['dealer_user_id' => $data['userId']]);
        $this->assertDatabaseMissing('dealer_user_permissions', ['dealer_user_id' => $data['userId']]);
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
