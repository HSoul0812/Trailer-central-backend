<?php

namespace App\Console\Commands\Website;

use Illuminate\Console\Command;
use App\Repositories\Website\RedirectRepositoryInterface;
use App\Traits\StreamCSVTrait;
use App\Models\Website\Website;
use Illuminate\Support\Facades\Log;

/**
 * Takes the data from a CSV in the following format:
 * 
 * redirect_from,redirect_to
 * google.com,google.com/a
 * google.com/b,google.com/c
 * google.com/d,google.com/e
 * 
 * And imports it into the website_redirect table
 */
class ImportRedirects extends Command {
    
    use StreamCSVTrait;
    
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "website:import:redirects {s3-bucket} {s3-key} {website-id} {srp-url}";
    
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
        $srpUrl = $this->argument('srp-url');
        $this->website = Website::findOrFail($this->websiteId);
        
        $this->streamCsv(function($csvData, $lineNumber) use ($srpUrl) {
            if ($lineNumber == 1) {
                return;
            }            

            $urlFrom = $this->removeUrlRoot($csvData[0]);
            $urlTo = $this->removeUrlRoot($csvData[1]);
            
            $urlTo = "/{$srpUrl}?stock=".$csvData[1];
                        
            try {
                $redirect = $this->websiteRedirectRepo->get(['from' => $urlFrom, 'to' => $urlTo, 'website_id' => $this->websiteId]);
            } catch (\Exception $ex) {                
                // Doesn't exist so create it
                $redirect = $this->addRedirect($urlFrom, $urlTo);
                \Log::info("Added redirect to {$this->websiteId} with id: {$redirect->identifier}");
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
        $finalUrl = $decomposedUrl['path'];
        if (isset($decomposedUrl['query'])) {
            $finalUrl .= '?' . $decomposedUrl['query'];
        }
        return $finalUrl;
//        $finalUrl = $url;
//        $websiteRootUrls = $this->getWebsiteRootUrls();
//        
//        foreach($websiteRootUrls as $websiteRootUrl) {            
//            $finalUrl = str_replace($websiteRootUrl, '', $url);
//            if ($finalUrl != $url) {
//                break;
//            }
//        }
//        
//        return $finalUrl;
    }
    
    private function getWebsiteRootUrls()
    {
        return [
            "https://{$this->website->domain}",
            "http://{$this->website->domain}",
            "https://www.{$this->website->domain}",
            "http://www.{$this->website->domain}",
        ];
    }
}
