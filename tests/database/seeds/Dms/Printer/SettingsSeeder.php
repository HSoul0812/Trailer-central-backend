<?php

declare(strict_types=1);

namespace Tests\database\seeds\Dms\Printer;

use App\Models\CRM\Dms\Printer\Settings;
use App\Models\User\User;
use App\Traits\WithGetter;
use Faker\Factory as Faker;
use Tests\database\seeds\Seeder;

/**
 * @property-read User $dealer
 */
class SettingsSeeder extends Seeder
{
    use WithGetter;

    /**
     * @var User
     */
    private $dealer;

    /**
     * InventorySeeder constructor.
     */
    public function __construct()
    {
        $this->dealer = factory(User::class)->create();
    }

    public function seed(): void
    {
        $dealerId = $this->dealer->getKey();
        
        $faker = Faker::create();
        
        Settings::create([
            'dealer_id' => $dealerId,
            'label_width' => $faker->numberBetween(10, 20),
            'label_height' => $faker->numberBetween(10, 20),
            'label_printer_dpi' => $faker->numberBetween(10, 20),
            'label_orientation' => 'landscape',
            'barcode_width' => $faker->numberBetween(10, 20),
            'barcode_height' => $faker->numberBetween(10, 20),
            'sku_price_font_size' => $faker->numberBetween(10, 20),
            'sku_price_x_position' => $faker->numberBetween(10, 20),
            'sku_price_y_position' => $faker->numberBetween(10, 20),
            'barcode_x_position' => $faker->numberBetween(10, 20),
            'barcode_y_position' => $faker->numberBetween(10, 20)
        ]);
    }

    public function cleanUp(): void
    {
        $dealerId = $this->dealer->getKey();
        // Database clean up
        Settings::where('dealer_id', $dealerId)->delete();
        User::destroy($dealerId);
    }

    public function getDealerId(): int
    {
        return $this->dealer->getKey();
    }
}
