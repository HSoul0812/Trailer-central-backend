<?php

namespace App\Console\Commands\Website;

use Illuminate\Console\Command;
use App\Repositories\Website\RedirectRepositoryInterface;
use App\Traits\StreamCSVTrait;
use App\Models\Website\Website;

class ImportRedirectsNonInventory extends Command {
    
    use StreamCSVTrait;
    
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "website:import:redirects-non-inventory {s3-bucket} {s3-key} {website-id}";
    
    /**
     * @var App\Repositories\Website\RedirectRepository
     */
    private $websiteRedirectRepo;    
    
    /**     
     * @var int
     */
    private $websiteId;
    
    /**     
     * @var App\Models\Website\Website
     */
    private $website;
        
    public function __construct() {
        $this->websiteRedirectRepo = app(RedirectRepositoryInterface::class);        
        parent::__construct();        
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    { 
        $this->s3Bucket = $this->argument('s3-bucket');
        $this->s3Key = $this->argument('s3-key');   
        $this->websiteId = $this->argument('website-id');
        $this->website = Website::findOrFail($this->websiteId);
        
        $this->streamCsv(function($csvData, $lineNumber) {
            if ($lineNumber == 1) {
                return;
            }            

            $urlFrom = $this->removeUrlRoot(current($csvData));
            $urlTo = $this->removeUrlRoot(end($csvData));
            
            if (empty($urlFrom) || empty($urlTo)) {
                return;
            }
                                    
            try {
                $redirect = $this->websiteRedirectRepo->get(['from' => $urlFrom, 'to' => $urlTo, 'website_id' => $this->websiteId]);
            } catch (\Exception $ex) {                
                // Doesn't exist so create it
                $redirect = $this->addRedirect($urlFrom, $urlTo);
                $this->info("Added redirect to {$this->websiteId} $urlFrom to $urlTo");
            }
            
        });        
    }
    
    private function addRedirect($urlFrom, $urlTo)
    {
        return $this->websiteRedirectRepo->create([
            'from' => $urlFrom,
            'to' => $urlTo,
            'website_id' => (int)$this->websiteId,
            'code' => 301
        ]);
    }
    
    private function removeUrlRoot($url)
    {
        $decomposedUrl = parse_url($url);
        
        if (!isset($decomposedUrl['path'])) {
            return '';
        }
        
        $finalUrl = $decomposedUrl['path'];
        if (isset($decomposedUrl['query'])) {
            $finalUrl .= '?' . $decomposedUrl['query'];
        }
        return $finalUrl;
    }
}
