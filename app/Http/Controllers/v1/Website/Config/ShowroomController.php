<?php
namespace App\Http\Controllers\v1\Website\Config;

use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\Website\Configuration\Showroom\SaveShowroomConfigRequest;
use App\Repositories\Website\Config\WebsiteConfigRepositoryInterface;
use App\Services\Website\WebsiteConfigServiceInterface;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;

/**
 * @deprecated This should be removed due we have an extra website config API which should handle any non-regular
 *             website variable e.g. call to action and showroom, both of them are regular website variables, so they
 *             should be ALWAYS handled by regular website variables API
 */
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
        $request = new SaveShowroomConfigRequest(array_merge($request->all(), ['websiteId' => $websiteId]));

        if (!$request->validate()) {
            return $this->response->errorBadRequest();
        }

        return $this->response->array($this->websiteConfigService->getShowroomConfig($request->all()));
    }

    private function createOrUpdate(int $websiteId, Request $request): Response
    {
        $request = new SaveShowroomConfigRequest(array_merge($request->all(), ['websiteId' => $websiteId]));

        if($request->validate()){
            return $this->response->array($this->websiteConfigService->getShowroomConfig($request->all()));
        }

        return $this->response->errorBadRequest();
    }

    /**
     * @param int $websiteId
     * @param Request $request
     * @return Response
     */
    public function create(int $websiteId, Request $request): Response
    {
        return $this->createOrUpdate($websiteId, $request);
    }

    /**
     * @param int $websiteId
     * @param Request $request
     * @return Response
     */
    public function update(int $websiteId, Request $request): Response
    {
        return $this->createOrUpdate($websiteId, $request);
    }
}
