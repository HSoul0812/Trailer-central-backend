<?php
namespace App\Http\Controllers\v1\Website\Config;

use App\Http\Controllers\RestfulControllerV2;
use App\Repositories\Website\Config\WebsiteConfigRepositoryInterface;
use App\Transformers\Website\Config\WebsiteConfigTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;

class ShowroomController extends RestfulControllerV2
{
    /** @var WebsiteConfigRepositoryInterface */
    private $websiteConfigRepository;

    /**
     * ShowroomController constructor.
     * @param WebsiteConfigRepositoryInterface $websiteConfigRepo
     */
    public function __construct(WebsiteConfigRepositoryInterface $websiteConfigRepo)
    {
        $this->websiteConfigRepository = $websiteConfigRepo;

        $this->middleware('setDealerIdOnRequest');
    }

    /**
     * @param int $websiteId
     * @param Request $request
     * @return Response
     */
    public function index(int $websiteId, Request $request) : Response
    {
        $requestData = $request->all();
        $requestData['websiteId'] = $websiteId;

        return $this->response->array($this->websiteConfigRepository->getShowroomConfig($requestData));
    }

    /**
     * @param int $websiteId
     * @return array
     */
    public function createOrUpdate(int $websiteId, Request $request) : Response
    {
        $requestData = $request->all();
        $requestData['websiteId'] = $websiteId;

        return $this->response->array($this->websiteConfigRepository->createOrUpdateShowroomConfig($requestData));
    }
}