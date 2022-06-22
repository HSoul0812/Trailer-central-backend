<?php

namespace App\Console\Commands\CRM\Dms;

use App\Models\Parts\Bin;
use App\Models\Parts\BinQuantity;
use App\Models\Parts\Part;
use App\Models\User\User;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InsertMissingPartBinMappings extends Command
{
    const CHUNK_SIZE = '5000';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crm:dms:insert-missing-part-bin-mappings {dealerId : Dealer ID to run this command on.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Insert the missing part -> bin mappings.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $dealer = User::findOrFail($this->argument('dealerId'));

        $binIds = Bin::query()
            ->where('dealer_id', $dealer->dealer_id)
            ->pluck('id');

        DB::table(Part::getTableName())
            ->select(['id', 'dealer_id'])
            ->where('dealer_id', $dealer->dealer_id)
            ->chunkById(self::CHUNK_SIZE, function (Collection $parts) use ($binIds) {
                DB::beginTransaction();

                $inserts = collect([]);

                foreach ($parts as $part) {
                    $partBinIds = DB::table(BinQuantity::getTableName())
                        ->where('part_id', $part->id)
                        ->pluck('bin_id');

                    $diffIds = $binIds->diff($partBinIds);
                    
                    $insertPayloads = $diffIds->map(function (int $binId) use ($part) {
                        echo "Insert: part_id = $part->id, bin_id = $binId\n";
                        return [
                            'part_id' => $part->id, 
                            'bin_id' => $binId, 
                            'created_at' => now(), 
                            'updated_at' => now()
                        ];
                    })->values();
                    
                    $inserts = $inserts->merge($insertPayloads);
                }

                DB::table(BinQuantity::getTableName())->insert($inserts->toArray());

                DB::commit();
            });

        return 0;
    }
}
