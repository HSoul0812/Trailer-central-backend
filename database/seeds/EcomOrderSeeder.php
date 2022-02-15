<?php

use Illuminate\Database\Seeder;
use App\Models\Ecommerce\CompletedOrder\CompletedOrder;


class EcomOrderSeeder extends Seeder
{

    
    private const AMOUNT_TO_CREATE = 20;
    
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {               
      $this->completedOrder = factory(CompletedOrder::class, self::AMOUNT_TO_CREATE)->create();
    }
}