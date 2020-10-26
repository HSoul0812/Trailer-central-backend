<?php


namespace App\Http\Controllers\v1\Dms;


use App\Http\Controllers\RestfulControllerV2;
use App\Repositories\Dms\SettingsRepositoryInterface;
use App\Transformers\Dms\SettingsTransformer;
use Dingo\Api\Http\Request;

class SettingsController extends RestfulControllerV2
{
    /**
     * @var SettingsRepositoryInterface
     */
    private $settingsRepository;
    /**
     * @var SettingsTransformer
     */
    private $transformer;

    public function __construct(SettingsRepositoryInterface $settingsRepository, SettingsTransformer $transformer)
    {
        $this->settingsRepository = $settingsRepository;
        $this->transformer = $transformer;

        $this->middleware('setDealerIdOnRequest')->only(['show', 'update']);
    }

    public function show(Request $request)
    {
        $settings = $this->settingsRepository->getByDealerId($request->input('dealer_id'));
        if (!$settings) {
            throw new \Exception('Settings not found for dealer.');
        }

        return $this->response->item($settings, $this->transformer);
    }

    public function update(Request $request)
    {
        $settings = $this->settingsRepository->getByDealerId($request->input('dealer_id'));
        $settings->fillWithMeta($request->all());
        $settings->save();

        return $this->response->accepted();
    }
}