<?php
namespace Tests\database\seeds\Ecommerce;

use App\Models\User\AuthToken;
use App\Models\User\User;
use App\Traits\WithGetter;
use Tests\database\seeds\Seeder;
use Faker\Factory as Faker;

/**
 * @property-read User $dealer
 * @property-read AuthToken $authToken
 * @property-read array $products
 * @property-read array $customerDetails
 * @property-read array $shippingDetails
 */
class ShippingSeeder extends Seeder
{
    use WithGetter;

    /**
     * @var User
     */
    private $dealer;

    /**
     * @var AuthToken
     */
    private $authToken;

    /** @var array */
    private $products = [];

    /** @var array */
    private $customerDetails = [];

    /** @var array  */
    private $shippingDetails = [];

    /**
     * ShippingSeeder constructor.
     */
    public function __construct()
    {
        $this->dealer = factory(User::class)->create();
    }

    public function seed(): void
    {
        $faker = Faker::create();

        $this->authToken = factory(AuthToken::class)->create([
            'user_id' => $this->dealer->dealer_id,
            'user_type' => AuthToken::USER_TYPE_DEALER,
        ]);

        $this->products[] = [
            'sku' => '9450014-CT',
            'qty' => 1
        ];

        $this->products[] = [
            'sku' => '9450311-CT',
            'qty' => 2
        ];

        $name = $faker->name;
        $lastName = $faker->lastName;
        $email = $faker->email;

        $this->customerDetails = [
            'customer' => [
                'firstname' => $name,
                'lastname' => $lastName,
                'email' => $email,
                'addresses' => []
            ],
            'password' => $faker->password,
        ];

        $this->shippingDetails = [
            'address' => [
                "region" => 'New York',
                "region_id" => 43,
                "region_code" => 'NY',
                "country_id" => 'US',
                "street" => [
                    '123 Oak Ave'
                ],
                "postcode" => '10577',
                "city" => 'Purchase',
                "firstname" => $name,
                "lastname" => $lastName,
                "customer_id" => 0,
                "email" => $email,
                "telephone" => "",
                "same_as_billing" => 1
            ]
        ];
    }

    public function cleanUp(): void
    {
        $dealerId = $this->dealer->getKey();

        AuthToken::where(['user_id' => $this->authToken->user_id, 'user_type' => AuthToken::USER_TYPE_DEALER])->delete();
        User::destroy($dealerId);
    }
}