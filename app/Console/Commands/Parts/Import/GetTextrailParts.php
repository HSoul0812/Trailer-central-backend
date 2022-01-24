<?php

namespace App\Console\Commands\Parts\Import;

use Illuminate\Console\Command;
use App\Services\Parts\Textrail\TextrailPartImporterServiceInterface;

class GetTextrailParts extends Command
{
    
    protected $textrailPartService;

   /**
    * The name and signature of the console command.
    *
    * @var string
    */

    protected $signature = 'command:get-textrail-parts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'daily command that import textrail parts to our database';

    public function __construct(TextrailPartImporterServiceInterface $textrailPartImporterService)
    {
        parent::__construct();
        $this->textrailPartImporterService = $textrailPartImporterService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
     public function handle()
     {
       $this->textrailPartImporterService->run();
     }

}
