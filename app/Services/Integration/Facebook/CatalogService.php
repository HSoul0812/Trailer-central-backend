<?php

namespace App\Services\Integration\Facebook;

use App\Jobs\Integration\Facebook\CatalogJob;
use App\Repositories\Integration\Auth\TokenRepositoryInterface;
use App\Repositories\Integration\Facebook\CatalogRepositoryInterface;
use App\Repositories\Integration\Facebook\PageRepositoryInterface;
use App\Services\Integration\AuthServiceInterface;
use App\Transformers\Integration\Facebook\CatalogTransformer;
use App\Utilities\Fractal\NoDataArraySerializer;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Bus\DispatchesJobs;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;

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
     * @var PageRepositoryInterface
     */
    protected $pages;

    /**
     * @var TokenRepositoryInterface
     */
    protected $tokens;

    /**
     * @var AuthServiceInterface
     */
    protected $auth;

    /**
     * @var BusinessServiceInterface
     */
    protected $sdk;

    /**
     * @var Manager
     */
    private $fractal;

    /**
     * Construct Facebook Service
     */
    public function __construct(
        CatalogRepositoryInterface $catalogs,
        PageRepositoryInterface $pages,
        TokenRepositoryInterface $tokens,
        AuthServiceInterface $auth,
        BusinessServiceInterface $sdk,
        Manager $fractal
    ) {
        $this->catalogs = $catalogs;
        $this->pages = $pages;
        $this->tokens = $tokens;
        $this->auth = $auth;
        $this->sdk = $sdk;
        $this->fractal = $fractal;

        $this->fractal->setSerializer(new NoDataArraySerializer());
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

        // Adjust Request
        $params['token_type'] = 'facebook';
        $params['relation_type'] = 'fbapp_catalog';
        $params['relation_id'] = $params['id'];
        unset($params['id']);

        // Get Access Token
        $accessToken = $this->tokens->getRelation($params);

        // Return Response
        return $this->response($catalog, $accessToken);
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
        $refresh = $this->auth->refresh($params);
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
            $refresh = $this->auth->refresh($params);
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

        // Access Token is Set?
        if(isset($params['access_token'])) {
            // Adjust Request
            $params['token_type'] = 'facebook';
            $params['relation_type'] = 'fbapp_catalog';
            $params['relation_id'] = $params['id'];
            unset($params['id']);

            // Find Refresh Token
            $refresh = $this->auth->refresh($params);
            if(!empty($refresh)) {
                $params['refresh_token'] = $refresh['access_token'];
                if(isset($refresh['expires_in'])) {
                    $params['expires_in'] = $refresh['expires_in'];
                    $params['expires_at'] = gmdate("Y-m-d H:i:s", (time() + $refresh['expires_in']));
                }
            }

            // Get Access Token
            $accessToken = $this->tokens->create($params);
        }

        // Page Token Exists?
        if(isset($params['page_token'])) {
            // Adjust Request
            $params['token_type'] = 'facebook';
            $params['relation_type'] = 'fbapp_page';
            $params['relation_id'] = $page->id;

            // Get Refresh Token
            $refresh = $this->auth->refresh($params);
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
        $catalog = $this->catalogs->get(['id' => $id]);

        // Feed ID Exists?!
        if(!empty($catalog->feed_id)) {
            $this->sdk->deleteFeed($catalog->accessToken, $catalog->feed_id);
        }

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
     * @param array $params
     * @return Fractal
     */
    public function payload($params) {
        // Parse Payload Data
        $payload = json_decode($params['payload']);
        $success = false;
        $feeds = array();
        foreach($payload as $integration) {
            // Validate Payload
            if(empty($integration->page_id)) {
                continue;
            }

            // Get Catalog
            $catalog = $this->catalogs->get(['id' => $integration->catalog_id]);
            if(empty($catalog->id)) {
                continue;
            }

            // Feed ID Exists?
            $feed = null;
            if(!empty($catalog->feed_id)) {
                try {
                    $feed = $this->sdk->validateFeed($catalog->page_token, $catalog->feed_id);
                } catch(\Exception $ex) {
                    Log::error("Exception returned during validate feed: " . $ex->getMessage() . ': ' . $ex->getTraceAsString());
                }
            }

            // Feed Doesn't Exist?
            if(empty($feed['id'])) {
                try {
                    $catalog->feed_id = 0;
                    $feed = $this->sdk->scheduleFeed($catalog->page_token, $catalog->feed_url, $catalog->feed_name);
                } catch(\Exception $ex) {
                    Log::error("Exception returned during schedule feed: " . $ex->getMessage() . ': ' . $ex->getTraceAsString());
                    continue;
                }
            }

            // Feed Exists?
            if(!empty($feed['id'])) {
                $feeds[] = $feed['id'];

                // Feed Doesn't Exist?
                if(empty($catalog->feed_id)) {
                    // Update Feed in Catalog
                    $catalog = $this->catalogs->update([
                        'id' => $catalog->id,
                        'feed_id' => $feed['id']
                    ]);
                }
            }

            // Create Job
            $this->dispatch(new CatalogJob($catalog, $integration));
        }

        // Validate Feeds Exist?
        if(count($feeds) > 0) {
            $success = true;
        }

        // Return Response
        return [
            'success' => $success,
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
    public function response($catalog, $accessToken) {
        // Convert Catalog to Array
        $data = new Item($catalog, new CatalogTransformer(), 'data');
        $response = $this->fractal->createData($data)->toArray();

        // Set Validate
        $response['validate'] = $this->auth->validate($accessToken);

        // Return Response
        return $response;
    }
}
