<?php

namespace App\Http\Controllers\v1\Website\Config;

use App\Repositories\Website\Config\WebsiteConfigRepositoryInterface;
use App\Http\Controllers\RestfulControllerV2;
use App\Transformers\Website\Config\WebsiteConfigTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;

/**
 * Class CallToActionController
 * @package App\Http\Controllers\v1\Website\Config
 */
class CallToActionController extends RestfulControllerV2
{
    /**
     * @var WebsiteConfigRepository
     */
    private $websiteConfigRepository;

    /**
     * WebsiteConfigController constructor.
     * @param WebsiteConfigRepositoryInterface $websiteConfigRepository
     */
    public function __construct(WebsiteConfigRepositoryInterface $websiteConfigRepository)
    {
        $this->websiteConfigRepository = $websiteConfigRepository;
    }
    
    /**
     * @param int $websiteId
     */
    public function index(int $websiteId, Request $request) : Response
    {
      return $this->response->collection($this->websiteConfigRepository->getAllCallToAction($websiteId), new WebsiteConfigTransformer);
    }

    /**
     * @param int $websiteId
     * @return array<WebsiteConfig>
     */
    public function createOrUpdate(int $websiteId, Request $request) : Response
    {
      return $this->response->array($this->websiteConfigRepository->createOrUpdate($websiteId, $request->all()));
    }

}