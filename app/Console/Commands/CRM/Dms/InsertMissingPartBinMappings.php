<?php

namespace App\Console\Commands\CRM\Dms;

use App\Models\Parts\Bin;
use App\Models\Parts\BinQuantity;
use App\Models\Parts\Part;
use App\Models\User\User;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InsertMissingPartBinMappings extends Command
{
    const CHUNK_SIZE = 5000;

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
        try {
            $dealer = User::findOrFail($this->argument('dealerId'));
        } catch (ModelNotFoundException $exception) {
            $this->error($exception->getMessage());
            return 1;
        }

        $binIds = Bin::query()
            ->where('dealer_id', $dealer->dealer_id)
            ->pluck('id');
        
        DB::beginTransaction();

        DB::table(Part::getTableName())
            ->select(['id', 'dealer_id'])
            ->where('dealer_id', $dealer->dealer_id)
            ->chunkById(self::CHUNK_SIZE, function (Collection $parts) use ($binIds) {
                $inserts = collect([]);
                
                $partIds = $parts->pluck('id');
                
                $partBins = DB::table(BinQuantity::getTableName())
                    ->whereIn('part_id', $partIds)
                    ->get(['id', 'part_id', 'bin_id'])
                    ->groupBy('part_id');
                
                /**
                 * @var int $partId
                 * @var Collection $bins
                 */
                foreach ($partBins as $partId => $bins) {
                    $partBinIds = $bins->pluck('bin_id');
                    
                    $diffIds = $binIds->diff($partBinIds);
                    
                    $insertPayloads = $diffIds->map(function (int $binId) use ($partId) {
                        $this->info("Insert: part_id = $partId, bin_id = $binId");
                        
                        return [
                            'part_id' => $partId, 
                            'bin_id' => $binId, 
                            'created_at' => now(), 
                            'updated_at' => now()
                        ];
                    })->values();
                    
                    $inserts = $inserts->merge($insertPayloads);
                }
                
                DB::table(BinQuantity::getTableName())->insert($inserts->toArray());
            });
        
        DB::commit();

        return 0;
    }
}
