<?php

namespace App\Services\Integration\Facebook;

use App\Services\Integration\Auth\FacebookServiceInterface;
use App\Repositories\Integration\Facebook\CatalogRepositoryInterface;
use Illuminate\Support\Facades\Log;

/**
 * Class CatalogService
 * 
 * @package App\Services\Integration\Facebook
 */
class CatalogService implements CatalogServiceInterface
{
    /**
     * @var FacebookServiceInterface
     */
    protected $sdk;

    /**
     * @var CatalogRepositoryInterface
     */
    protected $catalogs;

    /**
     * @var Manager
     */
    private $fractal;

    /**
     * Construct Facebook Service
     */
    public function __construct(
        FacebookServiceInterface $sdk,
        CatalogRepositoryInterface $catalog,
        AuthServiceInterface $auth,
        Manager $fractal
    ) {
        $this->sdk = $sdk;
        $this->catalogs = $catalog;
        $this->auth = $auth;
        $this->fractal = $fractal;

        $this->fractal->setSerializer(new NoDataArraySerializer());
    }

    /**
     * Get Catalog Response
     * 
     * @param array $params
     * @return Fractal
     */
    public function index($params) {
        // Get Catalogs
        $data = new Collection($this->catalogs->getAll($params), new CatalogTransformer(), 'data');
        return $this->fractal->createData($data)->toArray();
    }

    /**
     * Show Catalog Response
     * 
     * @param array $params
     * @return Fractal
     */
    public function show($params) {
        // Adjust Request
        $params['token_type'] = 'facebook';
        $params['relation_type'] = 'fbapp_catalog';
        $params['relation_id'] = $params['id'];
        unset($params['id']);

        // Get Access Token
        $accessToken = $this->tokens->getRelation($params);

        // Get Access Token
        $catalog = $this->catalogs->get(['id' => $params['relation_id']]);

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
        // Adjust Request
        $params['relation_type'] = 'fbapp_catalog';
        $params['relation_id'] = $params['id'];
        unset($params['id']);

        // Create Access Token
        $accessToken = $this->catalogs->create($params);

        // Return Response
        return $this->response($accessToken);
    }

    /**
     * Update Catalog
     * 
     * @param array $params
     * @return Fractal
     */
    public function update($params) {
        // Create Access Token
        $accessToken = $this->catalogs->update($params);

        // Return Response
        return $this->response($accessToken);
    }

    /**
     * Return Response
     * 
     * @param AccessToken $accessToken
     * @param array $response
     * @return array
     */
    public function response($accessToken, $response = array()) {
        // Convert Token to Array
        if(!empty($accessToken)) {
            $data = new Item($accessToken, new CatalogTransformer(), 'catalog');
            $token = $this->fractal->createData($data)->toArray();
            $response['data'] = $token['data'];
        } else {
            $response['data'] = null;
        }

        // Set Validate
        $response['validate'] = $this->validate($accessToken);

        // Return Response
        return $response;
    }
}
