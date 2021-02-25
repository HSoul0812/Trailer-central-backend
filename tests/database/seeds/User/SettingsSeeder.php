<?php

declare(strict_types=1);

namespace Tests\database\seeds\User;

use App\Models\User\Settings;
use App\Models\User\DealerLocation;
use App\Models\User\User;
use App\Models\Website\Website;
use App\Traits\WithGetter;
use Tests\database\seeds\Seeder;

/**
 * @property-read User $dealer
 * @property-read array<Settings> $missingSettings
 * @property-read array<Settings> $createdSettings
 */
class SettingsSeeder extends Seeder
{
    use WithGetter;

    /**
     * @var User
     */
    private $dealer;

    /**
     * @var Settings[]
     */
    private $missingSettings = [];

    /**
     * @var Settings[]
     */
    private $createdSettings = [];

    /**
     * SettingsSeeder constructor.
     */
    public function __construct()
    {
        $this->dealer = factory(User::class)->create();
    }

    public function seed(): void
    {
        $userId = $this->dealer->getKey();

        $seeds = [
            ['setting' => 'lock_state', 'value' => ''],
            ['setting' => 'inventory_table_color_mode', 'value' => ''],
            ['setting' => 'invoice_template', 'value' => ''],
            ['setting' => 'quote_print_template', 'value' => 'center_logo_with_country_field'],
            ['setting' => 'quote_print_header_content', 'value' => 'Test Header Content', 'create' => false],
            ['setting' => 'label_printer', 'value' => 'PDFWriter', 'create' => false]
        ];

        collect($seeds)->each(function (array $seed) use($userId): void {
            // Create Settings
            if(!isset($seed['create']) || $seed['create'] !== false) {
                // Make Settings
                $source = factory(Settings::class)->create([
                    'dealer_id' => $userId,
                    'setting' => $seed['setting'],
                    'setting_value' => $seed['value']
                ]);

                $this->createdSettings[] = $source;
                return;
            }

            // Make Settings
            $source = factory(Settings::class)->make([
                'dealer_id' => $userId,
                'setting' => $seed['setting'],
                'setting_value' => $seed['value']
            ]);

            $this->missingSettings[] = $source;
        });
    }

    public function cleanUp(): void
    {
        $dealerId = $this->dealer->getKey();

        // Database clean up
        Settings::where('dealer_id', $dealerId)->delete();
        DealerLocation::where('dealer_id', $dealerId)->delete();
        Website::destroy($dealerId);
        User::destroy($dealerId);
    }
}
