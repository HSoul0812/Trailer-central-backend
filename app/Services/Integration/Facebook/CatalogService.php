<?php

namespace App\Services\Integration\Facebook;

use App\Models\Integration\Auth\AccessToken;
use App\Models\Integration\Facebook\Catalog;
use App\Jobs\Integration\Facebook\Catalog\HomeJob;
use App\Jobs\Integration\Facebook\Catalog\ProductJob;
use App\Jobs\Integration\Facebook\Catalog\VehicleJob;
use App\Repositories\Integration\Auth\TokenRepositoryInterface;
use App\Repositories\Integration\Facebook\CatalogRepositoryInterface;
use App\Repositories\Integration\Facebook\FeedRepositoryInterface;
use App\Repositories\Integration\Facebook\PageRepositoryInterface;
use App\Transformers\Integration\Facebook\CatalogTransformer;
use App\Utilities\Fractal\NoDataArraySerializer;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Bus\DispatchesJobs;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Carbon\Carbon;

/**
 * Class CatalogService
 * 
 * @package App\Services\Integration\Facebook
 */
class CatalogService implements CatalogServiceInterface
{
    use DispatchesJobs;

    /**
     * @var CatalogRepositoryInterface
     */
    protected $catalogs;

    /**
     * @var FeedRepositoryInterface
     */
    protected $feeds;

    /**
     * @var PageRepositoryInterface
     */
    protected $pages;

    /**
     * @var TokenRepositoryInterface
     */
    protected $tokens;

    /**
     * @var BusinessServiceInterface
     */
    protected $sdk;

    /**
     * @var Manager
     */
    private $fractal;

    /**
     * Log
     */
    private $log;

    /**
     * Construct Facebook Service
     */
    public function __construct(
        CatalogRepositoryInterface $catalogs,
        FeedRepositoryInterface $feeds,
        PageRepositoryInterface $pages,
        TokenRepositoryInterface $tokens,
        BusinessServiceInterface $sdk,
        Manager $fractal
    ) {
        $this->catalogs = $catalogs;
        $this->feeds = $feeds;
        $this->pages = $pages;
        $this->tokens = $tokens;
        $this->sdk = $sdk;
        $this->fractal = $fractal;

        $this->fractal->setSerializer(new NoDataArraySerializer());

        // Initialize Logger
        $this->log = Log::channel('fb-catalog');
    }

    /**
     * Show Catalog Response
     * 
     * @param array $params
     * @return Fractal
     */
    public function show($params) {
        // Get Access Token
        $catalog = $this->catalogs->get($params);

        // Return Response
        return $this->response($catalog, $catalog->accessToken);
    }

    /**
     * Create Catalog
     * 
     * @param array $params
     * @return Fractal
     */
    public function create($params) {
        // Create Facebook Page
        $page = $this->pages->create($params);

        // Create Token
        $params['fbapp_page_id'] = $page->id;
        $catalog = $this->catalogs->create($params);

        // Adjust Request
        $params['token_type'] = 'facebook';
        $params['relation_type'] = 'fbapp_catalog';
        $params['relation_id'] = $catalog->id;

        // Find Refresh Token
        $refresh = $this->sdk->refresh($params);
        if(!empty($refresh)) {
            $params['refresh_token'] = $refresh['access_token'];
            if(isset($refresh['expires_in'])) {
                $params['expires_in'] = $refresh['expires_in'];
                $params['expires_at'] = gmdate("Y-m-d H:i:s", (time() + $refresh['expires_in']));
            }
        }

        // Get Access Token
        $accessToken = $this->tokens->create($params);

        // Page Token Exists?
        if(isset($params['page_token'])) {
            // Adjust Request
            $params['token_type'] = 'facebook';
            $params['relation_type'] = 'fbapp_page';
            $params['relation_id'] = $page->id;

            // Get Refresh Token
            $refresh = $this->sdk->refresh($params);
            if(!empty($refresh)) {
                $params['refresh_token'] = $refresh;
            } else {
                $params['refresh_token'] = NULL;
            }
            unset($params['id_token']);
            unset($params['page_token']);

            // Get Access Token
            $this->tokens->create($params);
        }

        // Return Response
        return $this->response($catalog, $accessToken);
    }

    /**
     * Update Catalog
     * 
     * @param array $params
     * @return Fractal
     */
    public function update($params) {
        // Update Facebook Page
        $page = $this->pages->create($params);

        // Create Access Token
        $catalog = $this->catalogs->update($params);

        // Adjust Request
        $params['token_type'] = 'facebook';
        $params['relation_type'] = 'fbapp_catalog';
        $params['relation_id'] = $params['id'];
        unset($params['id']);

        // Access Token is Set?
        if(isset($params['access_token']) && empty($params['refresh_token'])) {
            // Find Refresh Token
            $refresh = $this->sdk->refresh($params);
            if(!empty($refresh)) {
                $params['refresh_token'] = $refresh['access_token'];
                if(isset($refresh['expires_in'])) {
                    $params['expires_in'] = $refresh['expires_in'];
                    $params['expires_at'] = gmdate("Y-m-d H:i:s", (time() + $refresh['expires_in']));
                }
            }

            // Create Access Token
            $accessToken = $this->tokens->create($params);
        } else {
            // Get Access Token
            $accessToken = $this->tokens->getRelation($params);
        }

        // Page Token Exists?
        if(isset($params['page_token']) && empty($params['page_refresh_token'])) {
            // Adjust Request
            $params['token_type'] = 'facebook';
            $params['relation_type'] = 'fbapp_page';
            $params['relation_id'] = $page->id;

            // Get Refresh Token
            $refresh = $this->sdk->refresh($params);
            if(!empty($refresh)) {
                $params['refresh_token'] = $refresh;
            } else {
                unset($params['refresh_token']);
            }

            // Get Access Token
            $this->tokens->update($params);
        }

        // Return Response
        return $this->response($catalog, $accessToken);
    }

    /**
     * Delete Catalog
     * 
     * @param int $id
     * @return boolean
     */
    public function delete($id) {
        // Get Catalog
        $this->catalogs->get(['id' => $id]);

        // Delete Access Token
        $this->tokens->delete([
            'token_type' => 'facebook',
            'relation_type' => 'fbapp_catalog',
            'relation_id' => $id
        ]);

        // Delete Catalog
        return $this->catalogs->delete($id);
    }

    /**
     * Process Payload
     * 
     * @param string $payload
     * @return Fractal
     */
    public function payload(string $payload) {
        // Parse Payload Data
        $json = json_decode($payload);
        $feeds = [];
        foreach($json as $integration) {
            // Validate Payload
            if(empty($integration->business_id) && empty($integration->catalog_id)) {
                continue;
            }
            $this->log->debug('Handling Catalog #' . $integration->catalog_id . ' for Business #' . $integration->business_id);

            // Get Access Token and Feed ID
            $catalog = $this->catalogs->findOne(['catalog_id' => $integration->catalog_id]);
            $feedId = !empty($catalog->feed) ? $catalog->feed->feed_id : 0;
            if(empty($catalog->accessToken)) {
                $this->log->error('Catalog Access Token MISSING, Cannot Process Catalog #' . $integration->catalog_id);
                continue;
            }

            // Get Feed ID From SDK
            $feedId = $this->scheduleFeed($catalog->accessToken, $integration->business_id, $integration->catalog_id, $feedId);
            if(empty($feedId)) {
                $this->log->error('Feed Does Not Exist and Could Not Be Created for Catalog ID #' . $integration->catalog_id);
                continue;
            }

            // Feed Exists?
            $feed = $this->updateFeed($integration->business_id, $integration->catalog_id, $feedId);
            if(!empty($feed->feed_id)) {
                $feeds[] = $feed->feed_id;
            }

            // Create Job
            if($integration->catalog_type === Catalog::VEHICLE_TYPE) {
                $job = new VehicleJob($integration, $feed->feed_url);
            } elseif($integration->catalog_type === Catalog::HOME_TYPE) {
                $job = new HomeJob($integration, $feed->feed_url);
            } else {
                $job = new ProductJob($integration, $feed->feed_url);
            }

            // Dispatching Job
            $this->log->info('Dispatching a ' . $integration->catalog_type . ' Catalog Job for Catalog ID #' . $integration->catalog_id);
            $this->dispatch($job->onQueue('fb-catalog'));
        }

        // Return Response
        return [
            'success' => count($feeds) > 0,
            'feeds' => count($feeds)
        ];
    }

    /**
     * Return Response
     * 
     * @param Catalog $catalog
     * @param AccessToken $accessToken
     * @param array $response
     * @return array
     */
    public function response(Catalog $catalog, AccessToken $accessToken) {
        // Convert Catalog to Array
        $data = new Item($catalog, new CatalogTransformer(), 'data');
        $response = $this->fractal->createData($data)->toArray();

        // Set Validate
        $response['validate'] = $this->sdk->validate($accessToken);

        // Return Response
        return $response;
    }


    /**
     * Schedule Feed With Catalog Data
     * 
     * @param int $businessId
     * @param int $catalogId
     * @param int $feedId
     * @return int feed ID
     */
    private function scheduleFeed(AccessToken $accessToken, int $businessId, int $catalogId, int $feedId = 0) {
        // Feed ID Exists?
        if(!empty($feedId)) {
            try {
                $feed = $this->sdk->validateFeed($accessToken, $catalogId, $feedId);
                $feedId = $feed['id'];
            } catch(\Exception $ex) {
                $this->log->error("Exception returned during validate feed on Catalog ID #' .
                                    $catalogId . ': " . $ex->getMessage() . PHP_EOL .
                                    $ex->getTraceAsString());
            }
        }

        // Feed Doesn't Exist?
        if(empty($feedId)) {
            try {
                $feedUrl = $this->feeds->getFeedUrl($businessId, $catalogId);
                $feedName = $this->feeds->getFeedName($catalogId);
                $feed = $this->sdk->scheduleFeed($accessToken, $catalogId, $feedUrl, $feedName);
                $feedId = $feed['id'];
            } catch(\Exception $ex) {
                $this->log->error('Exception returned during schedule feed on Catalog ID #' .
                                    $catalogId . ': ' . $ex->getMessage() . PHP_EOL .
                                    $ex->getTraceAsString());
                $feedId = 0;
            }
        }

        // Return Feed ID
        return $feedId;
    }

    /**
     * Update Catalog Feed
     * 
     * Catalog $catalog
     * int $feedId
     */
    private function updateFeed(int $businessId, int $catalogId, int $feedId) {
        // Feed Exists?
        $feed = null;
        if(!empty($feedId)) {
            // Get Feed URL and Name
            $feedUrl = $this->feeds->getFeedUrl($businessId, $catalogId, false);
            $feedName = $this->feeds->getFeedName($catalogId);

            // Update Feed in Catalog
            $feed = $this->feeds->createOrUpdate([
                'business_id' => $businessId,
                'catalog_id' => $catalogId,
                'feed_id' => $feedId,
                'feed_title' => $feedName,
                'feed_url' => $feedUrl,
                'is_active' => 1,
                'imported_at' => Carbon::now()->toDateTimeString()
            ]);
        }

        // Return Feed
        return $feed;
    }
}
