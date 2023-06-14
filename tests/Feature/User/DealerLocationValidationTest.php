<?php

namespace Tests\Feature\User;

use App\Models\User\Location\Geolocation;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User\User;
use App\Repositories\User\DealerLocationRepositoryInterface;
use App\Models\User\AuthToken;
use App\Models\User\DealerLocation;

class DealerLocationValidationTest extends TestCase {

    use WithFaker;

    /** @var User */
    protected $dealer;

    /** @var AuthToken */
    protected $token;

    /** @var Geolocation */
    protected $geolocation;

    /** @var DealerLocationRepositoryInterface */
    protected $dealerLocationRepo;

    protected $dealerLocationId;

    const apiEndpoint = '/api/user/dealer-location';

    public function setUp(): void
    {
        parent::setUp();

        $this->dealer = factory(User::class)->create([
            'type' => User::TYPE_DEALER,
            'state' => User::STATUS_ACTIVE
        ]);

        $this->token = factory(AuthToken::class)->create([
            'user_id' => $this->dealer->dealer_id,
            'user_type' => AuthToken::USER_TYPE_DEALER,
        ]);

        $this->geolocation = factory(Geolocation::class)->create();

        $this->dealerLocationRepo = app(DealerLocationRepositoryInterface::class);
    }

    /**
     * @group DMS
     * @group DMS_DEALER_LOCATION
     *
     * @return void
     */
    public function testDealerLocation()
    {
        // PUT /api/user/dealer-location
        $formData = [
            'dealer_id' => $this->dealer->dealer_id,
            'name' => $this->faker->streetName(),
            'contact' => $this->faker->name(),
            'address' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'county' => $this->faker->city(),
            'region' => $this->faker->state(),
            'country' => 'USA',
            'is_default' => true,
            'postalcode' => $this->geolocation->zip,
            'phone' => '(970) 592-8015',
            'latitude' => $this->geolocation->latitude,
            'longitude' => $this->geolocation->longitude
        ];

        $response = $this->withHeaders(['access-token' => $this->token->access_token])
            ->putJson(self::apiEndpoint, $formData)
            ->assertSuccessful()
            ->assertJsonFragment([
                'name' => $formData['name'],
                'contact' => $formData['contact'],
                'address' => $formData['address'],
                'city' => $formData['city'],
                'county' => $formData['county'],
                'region' => $formData['region'],
                'country' => $formData['country'],
                'phone' => $formData['phone'],
                'dealer_id' => $formData['dealer_id'],
            ]);

        $this->dealerLocationId = $response->decodeResponseJson()['data']['id'];

        // POST /api/user/dealer-location/:Id
        $updatingFormData = [
            'name' => $this->faker->streetName(),
            'contact' => $this->faker->name(),
            'address' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'county' => $this->faker->city(),
            'region' => $this->faker->state(),
            'country' => 'USA',
            'postalcode' => $this->geolocation->zip,
            'phone' => '(267) 352-4031',
            'sms' => 1,
            'sms_phone' => '(979) 325-2092',
            'permanent_phone' => 1
        ];

        $response = $this->withHeaders(['access-token' => $this->token->access_token])
            ->postJson(self::apiEndpoint .'/'. $this->dealerLocationId, $updatingFormData)
            ->assertSuccessful()
            ->assertJsonFragment([
                'name' => $updatingFormData['name'],
                'contact' => $updatingFormData['contact'],
                'address' => $updatingFormData['address'],
                'city' => $updatingFormData['city'],
                'county' => $updatingFormData['county'],
                'region' => $updatingFormData['region'],
                'country' => $updatingFormData['country'],
                'sms_phone' => '+19793252092',
            ]);
    }

    /**
     * @group DMS
     * @group DMS_DEALER_LOCATION
     *
     * @return void
     */
    public function testDealerLocationWithoutOptional()
    {
        // PUT /api/user/dealer-location
        $formData = [
            'dealer_id' => $this->dealer->dealer_id,
            'name' => $this->faker->streetName(),
            'contact' => $this->faker->name(),
            'address' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'county' => $this->faker->city(),
            'region' => $this->faker->state(),
            'country' => 'USA',
            'is_default' => true,
            'postalcode' => $this->geolocation->zip,
            'phone' => '(970) 592-8015',
            'latitude' => $this->geolocation->latitude,
            'longitude' => $this->geolocation->longitude
        ];

        $response = $this->withHeaders(['access-token' => $this->token->access_token])
            ->putJson(self::apiEndpoint, $formData)
            ->assertSuccessful()
            ->assertJsonFragment([
                'name' => $formData['name'],
                'contact' => $formData['contact'],
                'address' => $formData['address'],
                'city' => $formData['city'],
                'county' => $formData['county'],
                'region' => $formData['region'],
                'country' => $formData['country'],
                'phone' => $formData['phone'],
                'dealer_id' => $formData['dealer_id'],
            ]);

        $this->dealerLocationId = $response->decodeResponseJson()['data']['id'];

        // POST /api/user/dealer-location/:Id
        $updatingFormData = [
            'city' => $this->faker->city(),
            'county' => $this->faker->city(),
            'region' => $this->faker->state(),
            'country' => 'USA',
            'phone' => '(267) 352-4031',
            'sms' => 1,
            'sms_phone' => '(979) 325-2092',
            'permanent_phone' => 1
        ];

        $response = $this->withHeaders(['access-token' => $this->token->access_token])
            ->postJson(self::apiEndpoint .'/'. $this->dealerLocationId, $updatingFormData)
            ->assertSuccessful()
            ->assertJsonFragment([
                'name' => $formData['name'],
                'contact' => $formData['contact'],
                'address' => $formData['address'],
                'city' => $updatingFormData['city'],
                'county' => $updatingFormData['county'],
                'region' => $updatingFormData['region'],
                'country' => $updatingFormData['country'],
                'sms_phone' => '+19793252092',
            ]);
    }

    public function validationDataProvider()
    {
        return [
            'SMS checkbox is checked but SMS Phone Number is empty' => [
                [
                    'sms' => 1,
                    'permanent_phone' => 0,
                    'sms_phone' => ''
                ],
                'sms_phone'
            ],
            'Country does not match Primary Phone Number' => [
                [
                    'phone' => '+56 2 3304 8683',
                    'country' => 'USA'
                ],
                'phone'
            ],
            'Country does not match SMS Phone Number' => [
                [
                    'sms' => 1,
                    'permanent_phone' => 1,
                    'sms_phone' => '+56 2 3304 8683',
                    'country' => 'USA'
                ],
                'sms_phone'
            ],
            'Country is not in range' => [
                [
                    'country' => 'NZ'
                ],
                'country'
            ],
            'Country match Primary Phone Number' => [
                [
                    'phone' => '+1 970 592 8015',
                    'country' => 'USA'
                ],
                null
            ],
            'Country match SMS Phone Number' => [
                [
                    'phone' => '+1 970 592 8015',
                    'sms' => 1,
                    'permanent_phone' => 0,
                    'sms_phone' => '+1 970 592 8015',
                    'country' => 'USA'
                ],
                null
            ],

        ];
    }

    /**
     * @dataProvider validationDataProvider
     *
     * @group DMS
     * @group DMS_DEALER_LOCATION
     */
    public function testValidation($testFormData, $failedValidationField)
    {
        $formData = [
            'dealer_id' => $this->dealer->dealer_id,
            'name' => $this->faker->streetName(),
            'contact' => $this->faker->name(),
            'address' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'county' => $this->faker->city(),
            'region' => $this->faker->state(),
            'country' => 'USA',
            'is_default' => true,
            'postalcode' => $this->geolocation->zip,
            'latitude' => $this->geolocation->latitude,
            'longitude' => $this->geolocation->longitude
        ];

        $formData = array_merge($formData, $testFormData);

        $response = $this->withHeaders(['access-token' => $this->token->access_token])
            ->json('PUT', self::apiEndpoint, $formData);

        if (empty($failedValidationField)) {

            $response->assertStatus(200)
            ->assertJsonMissingValidationErrors();

            $this->dealerLocationId = $response->decodeResponseJson()['data']['id'];

        } else {

            $response->assertStatus(422)
            ->assertJsonValidationErrors($failedValidationField);
        }
    }

    public function tearDown(): void
    {
        if ($this->dealerLocationId)
            $this->dealerLocationRepo->delete(['dealer_location_id' => $this->dealerLocationId]);

        $this->token->delete();
        $this->dealer->delete();
        $this->geolocation->delete();

        parent::tearDown();
    }

}
