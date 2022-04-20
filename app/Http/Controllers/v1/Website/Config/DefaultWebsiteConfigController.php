<?php

namespace App\Http\Controllers\v1\Website\Config;

use App\Repositories\Website\Config\DefaultConfigRepositoryInterface;
use App\Http\Controllers\RestfulControllerV2;
use App\Transformers\Website\Config\DefaultWebsiteConfigTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;

/**
 * Class DefaultWebsiteConfigController
 * @package App\Http\Controllers\v1\Website\Config
 */
class DefaultWebsiteConfigController extends RestfulControllerV2
{
    /**
     * @var DefaultConfigRepositoryInterface
     */
    private $defaultConfigRepository;

    /**
     * DefaultWebsiteConfigController constructor.
     * @param DefaultConfigRepositoryInterface $defaultConfigRepository
     */
    public function __construct(DefaultConfigRepositoryInterface $defaultConfigRepository)
    {
        $this->defaultConfigRepository = $defaultConfigRepository;
    }

    /**
     * @param Request $request
     */
    public function index(Request $request): Response
    {
        return $this->response->collection($this->defaultConfigRepository->getAll($request->all()), new DefaultWebsiteConfigTransformer);
    }
}
