<?php

declare(strict_types=1);

namespace Tests\database\seeds\Showroom;

use App\Models\Parts\Part;
use App\Models\Parts\CacheStoreTime;
use App\Models\Parts\Vendor;
use App\Models\Showroom\Showroom;
use App\Models\User\User;
use App\Traits\WithGetter;
use Faker\Factory as Faker;
use Tests\database\seeds\Seeder;

/**
 * @property-read User $dealer
 */
class ShowroomSeeder extends Seeder
{
    use WithGetter;

    /**
     * @var Showroom
     */
    private $showroom;

    /**
     * @var array
     */
    private $params;

    public function __construct()
    {
        //
    }

    public function seed(): void
    {
        $this->showroom = factory(Showroom::class)->create([
            "manufacturer" => "Testing Showroom",
            "year" => 2022,
            "engine_type" => "",
            "pull_type" => "bumper",
            "pull_type_extra" => "",
            "description" => " Standard Equipment\r\n\r\n    Coupler: 2\" RAM\r\n    Jack: 2000#\r\n    Fenders: 9x72 Rolled Straight\r\n    Floor: 2\" Treated Pine\r\n    Brake: Electric 2 Wheel\r\n    Break Away Unit w/ Charger\r\n    Stake Pockets\r\n    Standard Colors:\r\n    Black, Red, Blue, and Charcoal\r\n\r\n",
            "description_txt" => "Standard Equipment  \n  \n Coupler: 2\" RAM  \n Jack: 2000#  \n Fenders: 9x72 Rolled Straight  \n Floor: 2\" Treated Pine  \n Brake: Electric 2 Wheel  \n Break Away Unit w/ Charger  \n Stake Pockets  \n Standard Colors:  \n Black, Red, Blue, and Charcoal",
            "description_html" => "",
            "lq_price" => 0.00,
            "is_visible" => 1,
            "NEED_REVIEW" => 1,

        ]);
    }

    public function cleanUp(): void
    {
        // Database clean up
        Showroom::where('manufacturer', 'Testing Showroom')->delete();
    }

    public function getShowroomId(): int
    {
        return $this->showroom->getKey();
    }
}
