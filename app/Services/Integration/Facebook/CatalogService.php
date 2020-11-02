<?php

namespace App\Services\Integration\Facebook;

use App\Repositories\Integration\Facebook\CatalogRepositoryInterface;
use App\Services\Integration\AuthServiceInterface;
use App\Utilities\Fractal\NoDataArraySerializer;
use League\Fractal\Manager;

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
     * @var AuthServiceInterface
     */
    protected $auth;

    /**
     * @var Manager
     */
    private $fractal;

    /**
     * Construct Facebook Service
     */
    public function __construct(
        CatalogRepositoryInterface $catalog,
        AuthServiceInterface $auth,
        Manager $fractal
    ) {
        $this->catalogs = $catalog;
        $this->auth = $auth;
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
