<?php

namespace App\Http\Controllers\v1\Website\Config;

use App\Http\Requests\Website\Config\_Default\GetWebsiteConfigRequest;
use App\Models\Website\Config\WebsiteConfig;
use App\Models\Website\Config\WebsiteConfigDefault;
use App\Repositories\Website\Config\DefaultConfigRepositoryInterface;
use App\Repositories\Website\Config\WebsiteConfigRepository;
use App\Repositories\Website\Config\WebsiteConfigRepositoryInterface;
use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\Website\Config\CreateOrUpdateRequest;
use App\Transformers\Website\Config\DefaultWebsiteConfigRequestTransformer;
use App\Transformers\Website\Config\DefaultWebsiteConfigValueTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Illuminate\Support\Facades\App;

class WebsiteConfigController extends RestfulControllerV2
{
    /** @var WebsiteConfigRepository */
    private $websiteConfigRepository;

    /** @var DefaultConfigRepositoryInterface */
    private $defaultConfigRepository;

    public function __construct(WebsiteConfigRepositoryInterface $websiteConfigRepository,
                                DefaultConfigRepositoryInterface $defaultConfigRepository)
    {
        $this->websiteConfigRepository = $websiteConfigRepository;
        $this->defaultConfigRepository = $defaultConfigRepository;

        $this->middleware('setDealerIdOnRequest')->only(['index']);
    }

    /**
     * @param int $websiteId
     * @return Response|void
     * @throws \Dingo\Api\Exception\ResourceException when there were some validation error
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException when there was a bad request
     */
    public function index(int $websiteId, Request $request): Response
    {
        $request = new GetWebsiteConfigRequest(array_merge(['website_id' => $websiteId], $request->all()));

        if ($request->validate()) {
            /** @var DefaultWebsiteConfigValueTransformer $transformer */
            $transformer = App::make(DefaultWebsiteConfigValueTransformer::class, ['websiteId' => $websiteId]);

            $list = $this->defaultConfigRepository
                ->getAll($request->all())
                ->map(function (WebsiteConfigDefault $defaultConfig) use ($transformer) {
                    return $transformer->transform($defaultConfig);
                })
                ->groupBy('grouping')
                ->toArray();

            return $this->response->array(['data' => $list]);
        }

        $this->response->errorBadRequest();
    }

    /**
     * @param int $websiteId
     * @return array<WebsiteConfig>
     */
    public function createOrUpdate(int $websiteId, Request $request) : Response
    {
      $request = new CreateOrUpdateRequest($request->all());

      if (!$request->validate()) {
          return $this->response->errorBadRequest();
      }

      $transformer = new DefaultWebsiteConfigRequestTransformer();

      return $this->response->array($this->websiteConfigRepository->createOrUpdate($websiteId, $transformer->transform($request)));
    }
}
