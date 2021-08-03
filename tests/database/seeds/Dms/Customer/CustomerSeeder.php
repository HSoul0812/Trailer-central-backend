<?php

declare(strict_types=1);

namespace Tests\database\seeds\Dms\Customer;

use App\Models\CRM\User\Customer;
use App\Models\User\User;
use App\Traits\WithGetter;
use Tests\database\seeds\Seeder;
/**
 * @property-read array<array<Customer>> $customers
 * @property-read User[] $dealers
 */
class CustomerSeeder extends Seeder
{
    use WithGetter;

    /**
     * @var User[]
     */
    private $dealers = [];

    /**
     * @var array<array<Customer>>
     */
    private $customers = [];

    public function seed(): void
    {
        $this->dealers = factory(User::class, 2)->create();
        $this->customers[0] = factory(Customer::class, 2)->create(['dealer_id' => $this->dealers[0]->getKey()]);
        $this->customers[1] = factory(Customer::class, 2)->create(['dealer_id' => $this->dealers[1]->getKey()]);
    }

    public function cleanUp(): void
    {
        $dealersId = collect($this->dealers)->map(static function (User $dealer): int {
            return $dealer->getKey();
        });

        // Database clean up
        Customer::whereIn('dealer_id', $dealersId)->delete();
        User::whereIn('dealer_id', $dealersId)->delete();
    }

}
