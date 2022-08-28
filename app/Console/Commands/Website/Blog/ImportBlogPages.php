<?php

namespace App\Console\Commands\Website\Blog;

use Illuminate\Console\Command;
use App\Repositories\Website\WebsiteRepositoryInterface;
use App\Repositories\Website\Blog\PostRepositoryInterface;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\Website\Blog\Post;
use GuzzleHttp\Client as GuzzleHttpClient;
use App\Traits\StreamCSVTrait;
/**
 * Imports blog pages from CSV
 */
class ImportBlogPages extends Command {
    
    use StreamCSVTrait;
        
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "website:blog:import {s3-bucket} {s3-key} {website-id} {old-blog-domain}"; 

    /**     
     * @var int 
     */
    protected $websiteId;
    
    /**     
     * @var App\Repositories\Website\WebsiteRepositoryInterface 
     */
    protected $websiteRepo;
    
    /**     
     * @var PostRepositoryInterface 
     */
    protected $postRepo;
    
    /**     
     * @var GuzzleHttp\Client
     */
    protected $httpClient;
    
    /**     
     * @var array
     */
    private $oldBlogDomain;
    
    /**
     * AddSitemaps constructor.
     * @param WebsiteRepositoryInterface $websiteRepository
     */
    public function __construct(WebsiteRepositoryInterface $websiteRepository, PostRepositoryInterface $postRepository)
    {
        parent::__construct();
        $this->websiteRepo = $websiteRepository;
        $this->postRepo = $postRepository;
        $this->httpClient = new GuzzleHttpClient();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() : void
    { 
        $this->websiteId = $this->argument('website-id');
        $this->s3Key = $this->argument('s3-key');   
        $this->s3Bucket = $this->argument('s3-bucket');
        $this->oldBlogDomain = $this->argument('old-blog-domain');
        
        if (strpos($this->oldBlogDomain, ',') !== false) {
            $this->oldBlogDomain = explode(',', $this->oldBlogDomain);
        } else {
            $this->oldBlogDomain = [$this->oldBlogDomain];
        }
        
        $website = $this->websiteRepo->get(['id' => $this->websiteId]);
        
        $this->streamCsv(function($csvData, $lineNumber) {
            if ($lineNumber === 1) {
                return;
            }   
            
            list($title, $body, $url) = $csvData;

            $title = strip_tags($title);
            foreach($this->oldBlogDomain as $blogDomain) {
                $newUrl = str_replace("https://www.{$blogDomain}/", '', $url);
                
                if ($url != $newUrl) {
                    $url = $newUrl;
                    break;
                }
            }
            
            try {
                $body = $this->uploadBlogImagesToS3($body);
            } catch (\Exception $ex) {

            }
            
            $title = explode('-', $title)[0];
            
            $this->postRepo->create([
                'title' => $title,
                'url_path' => $url,
                'status' => Post::STATUS_PUBLISHED,
                'post_content' => $body,
                'website_id' => $this->websiteId
            ]);
            
            $this->info($url . " entry inserted");
            
        });   
    }
    
    private function uploadBlogImagesToS3(string $blogBody) : string
    {        
        $replace = [];
        
        $crawler = new Crawler($blogBody);
        
        $result = $crawler->filter('img')->extract(array('src'));
        
        foreach($result as $img) {
            $explodedImage = explode('/', $img);
            $imageName = 'blog-files/'.$explodedImage[count($explodedImage) - 1];
            if ( strpos($img, 'https') === false ) {
                $img = "https:".$img;
            }
            $result = Storage::disk('s3')->put($imageName, file_get_contents($img));
            if ($result) {
                $replace[$img] = 'https://'.env('AWS_BUCKET').'.s3.amazonaws.com/'.$imageName;
            }            
        }
            
        foreach($replace as $originalImage => $newImage) {
            $blogBody = str_replace($originalImage, $newImage, $blogBody);
        }
        
        return $blogBody;
    }
        
}
