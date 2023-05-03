<?php

/** @noinspection PhpDocSignatureIsNotCompleteInspection */

declare(strict_types=1);

namespace Database\Seeders\Glossary;

use App\Models\Glossary\Glossary;
use Illuminate\Database\Seeder;

class GlossarySeeder extends Seeder
{
    private const GLOSSARY = [
      ['denomination' => 'Keywords', 'short_description' => 'Type your search and the cell will autosuggest keywords from our database.', 'long_description' => ''],
      ['denomination' => 'Deal', 'short_description' => 'Special offers can be filtered here.', 'long_description' => ''],
      ['denomination' => 'Sale', 'short_description' => 'Filter only products with discounted sales price.', 'long_description' => ''],
      ['denomination' => 'Price', 'short_description' => 'Select a price range to filter products that fit your budget.', 'long_description' => ''],
      ['denomination' => 'Condition', 'short_description' => 'Our products range from new to used state.', 'long_description' => '(refurbished, reconditioned, reviewed)'],
      ['denomination' => 'Remanufactured', 'short_description' => 'Used products that are verified or refurbished by dealers.', 'long_description' => 'Used products that have been verified and put back to condition by professional dealers. They are usually sold with some level of warranty by the seller.'],
      ['denomination' => 'Size', 'short_description' => 'Dimensions of the floor / cargo area', 'long_description' => ''],
      ['denomination' => 'Length', 'short_description' => 'Length of the cargo floor.', 'long_description' => ''],
      ['denomination' => 'Width', 'short_description' => 'Width of the cargo floor.', 'long_description' => ''],
      ['denomination' => 'Height', 'short_description' => 'Height of the cargo area.', 'long_description' => ''],
      ['denomination' => 'GVWR', 'short_description' => 'The maximum loaded weight of the vehicle or trailer.', 'long_description' => 'The maximum loaded weight of the vehicle or trailer as determined by the manufacturer. This comprises the weight of the cargo and also the vehicle itself. The GVWR can be found on the trailer’s VIN plate. '],
      ['denomination' => 'Payload capacity', 'short_description' => 'The maximum amount of weight a trailer can safely carry.', 'long_description' => 'The payload capacity refers to the total weight that can be carried by a trailer. The maximum payload capacity is equal to the GVWR subtracted of the trailer weight.'],
      ['denomination' => 'Features (FLF)', 'short_description' => 'Features that have to be on the product.', 'long_description' => 'The sellers have the possibility of selecting multiple features present on their product. Select those features which you require.'],
      ['denomination' => 'Jack & Coupler', 'short_description' => 'How the jack operates and size of the coupler.', 'long_description' => 'Depending on the weight of the trailer and cargo it will carry, consider a jack that will be easier to operate. Note that there are different dimensions for the coupler. '],
      ['denomination' => 'Wheels & Suspension', 'short_description' => 'Select # of wheel holes, type of braking and type of suspensions.',
      'long_description' => 'With the type of cargo you’ll carry on the trailer, you’ll want to adjust your filtering of wheels, brake and suspensions.'],
      ['denomination' => 'Structural Features', 'short_description' => 'The upright and crossmember structures of the trailers.',
      'long_description' => 'The structural features will determine the possibilities flooring and wall attachments to the trailer.'],
    ];

    /**
     * Run the database seeds.
     */
    public function run()
    {
        $this->cleanTables();

        foreach (self::GLOSSARY as $glossary) {
            $new_glossary = Glossary::create([
              'denomination' => $glossary['denomination'],
              'short_description' => $glossary['short_description'],
              'long_description' => $glossary['long_description'],
              'type' => 'filters',
          ]);
        }
    }

    private function cleanTables(): void
    {
        Glossary::truncate();
    }
}
