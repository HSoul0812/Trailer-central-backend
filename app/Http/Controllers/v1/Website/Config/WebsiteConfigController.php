<?php

namespace App\Http\Controllers\v1\Website\Config;

use App\Repositories\Website\Config\WebsiteConfigRepositoryInterface;
use App\Http\Controllers\RestfulController;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;

/**
 * Class WebsiteConfigController
 * @package App\Http\Controllers\v1\Website\Config
 */
class WebsiteConfigController extends RestfulController
{
    /**
     * @var WebsiteService
     */
    private $websiteService;

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
    public function getCallToAction(int $websiteId): object
    {
      return $this->websiteConfigRepository->getAllCallToAction($websiteId);
    }

    /**
     * @param int $websiteId
     * @return array<WebsiteConfig>
     */
    public function configCallToAction(int $websiteId, Request $request): array
    {
      return $this->websiteConfigRepository->createOrUpdateCallToAction($websiteId, $request->all());
    }

}