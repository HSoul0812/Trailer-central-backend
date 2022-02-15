<?php

namespace App\Console\Commands\CRM\Dms\CVR;

use Illuminate\Console\Command;
use App\Models\CRM\Dms\UnitSale;
use App\Services\Dms\CVR\CVRGeneratorServiceInterface;

/**
 * Used to test CVR 
 */
class GenerateCVRDocumentCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crm:dms:generate-cvr-document {unit-sale-id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates a CVR document for the given unit sale.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(CVRGeneratorServiceInterface $cvrService)
    {
        $unitSale = UnitSale::findOrFail($this->argument('unit-sale-id'));
        $cvrFile = $cvrService->generate($unitSale);
        $this->info($cvrFile->getFilePath());
    }
}
