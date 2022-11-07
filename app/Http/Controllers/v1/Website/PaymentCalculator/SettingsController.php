<?php

namespace App\Http\Controllers\v1\Website\PaymentCalculator;

use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\Website\PaymentCalculator\DeleteSettingsRequest;
use App\Models\Website\PaymentCalculator\Settings;
use Dingo\Api\Http\Request;
use App\Repositories\Website\PaymentCalculator\SettingsRepositoryInterface;
use App\Http\Requests\Website\PaymentCalculator\GetSettingsRequest;
use App\Transformers\Website\PaymentCalculator\SettingsTransformer;
use App\Http\Requests\Website\PaymentCalculator\CreateSettingsRequest;
use App\Http\Requests\Website\PaymentCalculator\UpdateSettingsRequest;
use Dingo\Api\Http\Response;

class SettingsController extends RestfulControllerV2
{

    /** @var SettingsRepositoryInterface */
    protected $settings;

    /** @var SettingsTransformer */
    private $transformer;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(SettingsRepositoryInterface $settings)
    {
        $this->settings = $settings;

        $this->middleware('setDealerIdOnRequest')->only(['destroy', 'index', 'create', 'update']);

        $this->transformer = new SettingsTransformer();
    }

    public function index(int $websiteId, Request $request): Response
    {
        $request = new GetSettingsRequest($request->all() + ['website_id' => $websiteId]);

        if (!$request->validate()) {
            $this->response->errorBadRequest();
        }

        if ($request->grouped) {
            return $this->response->array(['data' => $this->settings
                ->getAll($request->all())
                ->map(function (Settings $defaultConfig): array {
                    return $this->transformer->transform($defaultConfig);
                })
                ->groupBy('entity.name')
                ->toArray()]);
        }

        return $this->response->collection($this->settings->getAll($request->all()), $this->transformer);
    }

    public function create(int $websiteId, Request $request): Response
    {
        $request = new CreateSettingsRequest($request->all() + ['website_id' => $websiteId]);

        if (!$request->validate()) {
            $this->response->errorBadRequest();
        }

        return $this->response->item($this->settings->create($request->all()), new SettingsTransformer());
    }

    public function update(int $websiteId, int $id, Request $request): Response
    {
        $request = new UpdateSettingsRequest($request->all() + ['website_id' => $websiteId, 'id' => $id]);

        if (!$request->validate()) {
            $this->response->errorBadRequest();
        }

        return $this->response->item($this->settings->update($request->all()), $this->transformer);
    }

    public function destroy(int $websiteId, int $id, Request $request): Response
    {
        $request = new DeleteSettingsRequest($request->all() + ['website_id' => $websiteId, 'id' => $id]);

        if (!$request->validate()) {
            $this->response->errorBadRequest();
        }

        $this->settings->delete(['id' => $request->id]);

        return $this->response->noContent();
    }
}
