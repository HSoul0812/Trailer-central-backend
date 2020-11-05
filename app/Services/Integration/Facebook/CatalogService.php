<?php

namespace App\Services\Integration\Facebook;

use App\Repositories\Integration\Auth\TokenRepositoryInterface;
use App\Repositories\Integration\Facebook\CatalogRepositoryInterface;
use App\Services\Integration\AuthServiceInterface;
use App\Utilities\Fractal\NoDataArraySerializer;
use App\Transformers\Integration\Facebook\CatalogTransformer;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;

/**
 * Class CatalogService
 * 
 * @package App\Services\Integration\Facebook
 */
class CatalogService implements CatalogServiceInterface
{
    /**
     * @var CatalogRepositoryInterface
     */
    protected $catalogs;

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
        CatalogRepositoryInterface $catalog,
        TokenRepositoryInterface $tokens,
        AuthServiceInterface $auth,
        BusinessServiceInterface $sdk,
        Manager $fractal
    ) {
        $this->catalogs = $catalog;
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
        // Create Token
        $catalog = $this->catalogs->create($params);

        // Adjust Request
        $params['token_type'] = 'facebook';
        $params['relation_type'] = 'fbapp_catalog';
        $params['relation_id'] = $catalog->id;

        // Get Access Token
        $accessToken = $this->tokens->create($params);

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
        // Create Access Token
        $catalog = $this->catalogs->update($params);

        // Adjust Request
        $params['token_type'] = 'facebook';
        $params['relation_type'] = 'fbapp_catalog';
        $params['relation_id'] = $params['id'];
        unset($params['id']);

        // Get Access Token
        $accessToken = $this->tokens->create($params);

        // Return Response
        return $this->response($catalog, $accessToken);
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
            $catalog = $this->catalogs->getByPageId(['page_id' => $integration->page_id]);

            // Feed ID Exists?
            $feed = null;
            if(!empty($catalog->feed_id)) {
                $feed = $this->sdk->validateFeed($catalog->feed_id);
            }

            // Feed Doesn't Exist?
            if(empty($feed)) {
                $feed = $this->sdk->scheduleFeed($catalog->access_token, $catalog->feed_url, $catalog->feed_name);
            }

            // Feed Exists?
            if(!empty($feed)) {
                $feeds[] = $feed;
            }

            // Update Feed in Catalog
            var_dump($feed);

            // Create Job
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
    public function response($catalog, $accessToken, $response = array()) {
        // Convert Token to Array
        if(!empty($catalog)) {
            $data = new Item($catalog, new CatalogTransformer(), 'data');
            $item = $this->fractal->createData($data)->toArray();
            $response['catalog'] = $item['data'];
        } else {
            $response['catalog'] = null;
        }

        // Return Response
        return $this->auth->response($accessToken, $response);
    }
}
