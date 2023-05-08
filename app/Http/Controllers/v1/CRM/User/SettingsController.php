<?php

namespace App\Http\Controllers\v1\CRM\User;

use App\Http\Controllers\RestfulController;
use App\Services\CRM\User\SettingsServiceInterface;
use App\Http\Requests\CRM\User\GetSettingsRequest;
use App\Http\Requests\CRM\User\UpdateSettingsRequest;
use App\Transformers\CRM\User\SettingsTransformer;
use Dingo\Api\Http\Request;

/**
 * Class SettingsController
 * @package App\Http\Controllers\v1\CRM\User
 */
class SettingsController extends RestfulController
{
    /**
     * @var SettingsServiceInterface
     */
    private $service;

    public function __construct(SettingsServiceInterface $service)
    {
        $this->service = $service;

        $this->middleware('setUserIdOnRequest')->only(['index', 'updateSettings']);
    }

    /**
     * @param Request $request
     */
    public function index(Request $request)
    {
        $request = new GetSettingsRequest($request->all());

        if ($request->validate()) {

            return $this->response->item($this->service->getAll($request->all()), new SettingsTransformer);
        }
        
        return $this->response->errorBadRequest();
    }

    /**
     * @param Request $request
     */
    public function updateSettings(Request $request)
    {
        $request = new UpdateSettingsRequest($request->all());

        if ($request->validate()) {

            return $this->response->array(['data' => $this->service->update($request->all())]);
        }
        
        return $this->response->errorBadRequest();
    }
}