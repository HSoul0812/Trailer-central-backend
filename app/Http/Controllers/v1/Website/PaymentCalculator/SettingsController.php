<?php

namespace App\Http\Controllers\v1\Website\PaymentCalculator;

use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\Website\PaymentCalculator\DeleteSettingsRequest;
use Dingo\Api\Http\Request;
use App\Repositories\Website\PaymentCalculator\SettingsRepositoryInterface;
use App\Http\Requests\Website\PaymentCalculator\GetSettingsRequest;
use App\Transformers\Website\PaymentCalculator\SettingsTransformer;
use App\Http\Requests\Website\PaymentCalculator\CreateSettingsRequest;
use App\Http\Requests\Website\PaymentCalculator\UpdateSettingsRequest;
use Dingo\Api\Http\Response;

class SettingsController extends RestfulControllerV2 {

    /** @var SettingsRepositoryInterface  */
    protected $settings;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(SettingsRepositoryInterface $settings)
    {
        $this->settings = $settings;

        $this->middleware('setDealerIdOnRequest')->only(['destroy']);
    }


    /**
     * @OA\Get(
     *     path="/api/website/{websiteId}/payment-calculator/settings",
     *     description="Retrieve a list of filters",
     *     tags={"Website Part Filters"},
     *     @OA\Response(
     *         response="200",
     *         description="Returns a list of parts",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     */
    public function index(Request $request)
    {
        $request = new GetSettingsRequest($request->all());

        if ($request->validate()) {
            return $this->response->collection($this->settings->getAll($request->all()), new SettingsTransformer);
        }

        return $this->response->errorBadRequest();
    }

    public function create(Request $request) {
        $request = new CreateSettingsRequest($request->all());

        if ( $request->validate() ) {
            return $this->response->item($this->settings->create($request->all()), new SettingsTransformer());
        }

        return $this->response->errorBadRequest();
    }

    public function update(int $id, Request $request) {
        $requestData = $request->all();
        unset($requestData['dealer_id']);

        $request = new UpdateSettingsRequest($requestData);

        if ( $request->validate() ) {
            return $this->response->item($this->settings->update($request->all()), new SettingsTransformer());
        }

        return $this->response->errorBadRequest();
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @throws \App\Exceptions\Requests\Validation\NoObjectIdValueSetException
     * @throws \App\Exceptions\Requests\Validation\NoObjectTypeSetException
     */
    public function destroy(Request $request): Response
    {
        $request = new DeleteSettingsRequest($request->all());

        if (!$request->validate()) {
            $this->response->errorBadRequest();
        }

        $this->settings->delete(['id' => $request->id]);

        return $this->response->noContent();
    }
}
