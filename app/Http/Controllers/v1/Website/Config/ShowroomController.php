<?php
namespace App\Http\Controllers\v1\Website\Config;

use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\Website\Configuration\Showroom\GetShowroomConfigRequest;
use App\Repositories\Website\Config\WebsiteConfigRepositoryInterface;
use App\Services\Website\WebsiteConfigServiceInterface;
use App\Transformers\Website\Config\WebsiteConfigTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;

class ShowroomController extends RestfulControllerV2
{

    /** @var WebsiteConfigServiceInterface */
    private $websiteConfigService;

    /**
     * ShowroomController constructor.
     * @param WebsiteConfigServiceInterface $websiteConfigService
     */
    public function __construct(WebsiteConfigServiceInterface $websiteConfigService)
    {
        $this->websiteConfigService = $websiteConfigService;

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

        $request = new GetShowroomConfigRequest($requestData);

        if (!$request->validate()) {
            return $this->response->errorBadRequest();
        }

        return $this->response->array($this->websiteConfigService->getShowroomConfig($requestData));
    }

    /**
     * @param int $websiteId
     * @param Request $request
     * @return Response
     */
    public function create(int $websiteId, Request $request): Response
    {
        $requestData = $request->all();
        $requestData['websiteId'] = $websiteId;

        $this->websiteConfigService->createShowroomConfig($requestData);

        return $this->response->array($this->websiteConfigService->getShowroomConfig($requestData));
    }

    /**
     * @param int $websiteId
     * @param Request $request
     * @return Response
     */
    public function update(int $websiteId, Request $request): Response
    {
        $requestData = $request->all();
        $requestData['websiteId'] = $websiteId;

        $this->websiteConfigService->createShowroomConfig($requestData);

        return $this->response->array($this->websiteConfigService->getShowroomConfig($requestData));
    }
}