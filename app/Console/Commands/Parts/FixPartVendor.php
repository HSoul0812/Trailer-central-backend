<?php

namespace App\Console\Commands\Parts;

use Illuminate\Console\Command;

use App\Models\Parts\Part;
use App\Models\Parts\Vendor;

/**
 * Class FixPartVendor
 * @package App\Console\Commands\Parts
 */
class FixPartVendor extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'parts:fix-vendor {dealer_id?}';

    /**
     * The console command description
     * 
     * @var string
     */
    protected $description = 'Associate a part with one of vendors of same dealer, instead of other dealer\'s vendor.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(): bool
    {
        $dealerId = $this->argument('dealer_id');
        $count = 0;

        $partsQuery = Part::with('vendor')
            ->whereHas('vendor', function($query) {
                $query->whereRaw('parts_v1.dealer_id <> qb_vendors.dealer_id');
            });
        if (!empty($dealerId)) {
            $partsQuery = $partsQuery->where('dealer_id', '=', $dealerId);
        }

        $partsQuery->chunk(500, function($parts) use($count){
            $part = $parts[0];
            foreach ($parts as $part) {
                $vendor = Vendor::where([
                    ['dealer_id', '=', $part->dealer_id],
                    ['name', '=', $part->vendor->name]
                ])->first();

                if (empty($vendor)) {
                    $vendor = Vendor::create([
                        'name' => $part->vendor->name,
                        'dealer_id' => $part->dealer_id,
                        'auto_created' => 1,
                        'notes' => 'Prev vendor: ' . $part->vendor->id
                    ]);
                }

                $part->vendor_id = $vendor->id;
                $part->save();

                $count++;
            }

            $this->info($count . ' parts have been updated.');
        });

        return true;
    }
}
