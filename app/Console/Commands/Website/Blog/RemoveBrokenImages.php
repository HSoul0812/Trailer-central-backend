<?php

namespace App\Console\Commands\Website\Blog;

use Illuminate\Console\Command;
use App\Repositories\Website\WebsiteRepositoryInterface;
use Symfony\Component\DomCrawler\Crawler;
use GuzzleHttp\Client as GuzzleHttpClient;

/**
 * Removes blog post broken images
 */
class RemoveBrokenImages extends Command {
    
    const REPLACE_IMAGE = 'https://dealer-cdn.com/bishs_logo.jpg';
    
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "website:blog:remove-broken-images {websiteId}"; 

    /**     
     * @var int 
     */
    protected $websiteId;
    
    /**     
     * @var App\Repositories\Website\WebsiteRepositoryInterface 
     */
    protected $websiteRepo;
    
    /**     
     * @var GuzzleHttp\Client
     */
    protected $httpClient;
    
    /**
     * AddSitemaps constructor.
     * @param WebsiteRepositoryInterface $websiteRepository
     */
    public function __construct(WebsiteRepositoryInterface $websiteRepository)
    {
        parent::__construct();
        $this->websiteRepo = $websiteRepository;
        $this->httpClient = new GuzzleHttpClient();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    { 
        $this->websiteId = $this->argument('websiteId');
        $website = $this->websiteRepo->get(['id' => $this->websiteId]);
        foreach($website->blogPosts as $post) {
            $crawler = new Crawler($post->post_content);
            
            $result = $crawler->filter('img')->extract(array('src'));
            
            foreach($result as $img) {
                try {
                    if ($this->isImageBroken($img)) {
                        $post->post_content = str_replace($img, self::REPLACE_IMAGE, $post->post_content);
                    } 
                } catch (\Exception $ex) {
                    // Assume broken
                    $post->post_content = str_replace($img, self::REPLACE_IMAGE, $post->post_content);
                }
                
            }
            
            $post->save();
        }     
    }
    
    private function isImageBroken($img)
    {
        $response = $this->httpClient->get($img);
        
        if ($response->getStatusCode() != 200) {
            return true;
        }
        
        $contentType = current($response->getHeaders()['Content-Type']);
        if (strpos($contentType, 'image') === false) {
            // It's not an image
            return true;
        }

        return false;
    }
    
}
