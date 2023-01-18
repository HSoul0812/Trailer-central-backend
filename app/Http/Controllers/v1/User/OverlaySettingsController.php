<?php

namespace App\Http\Controllers\v1\User;

use App\Http\Controllers\RestfulController;
use App\Transformers\User\OverlaySettingsTransformer;
use App\Http\Requests\User\GetOverlaySettingsRequest;
use App\Http\Requests\User\UpdateOverlaySettingsRequest;
use Illuminate\Support\Facades\Storage;
use App\Models\User\User;
use Dingo\Api\Http\Request;
use App\Services\Inventory\ImageServiceInterface;

class OverlaySettingsController extends RestfulController 
{    
    /**
     * @var ImageServiceInterface
     */
    protected $imageService;
    
    /**
     * @var AutoImportSettingsTransformer 
     */
    protected $transformer;
    
    public function __construct(ImageServiceInterface $imageService)
    {
        $this->middleware('setDealerIdOnRequest')->only([
            'index', 'updateSettings'
        ]);
        
        $this->imageService = $imageService;
        
        $this->transformer = new OverlaySettingsTransformer;
    }
    
    /**
     * Displays a list of all records in the DB.
     * Paginated or not paginated
     */
    public function index(Request $request) 
    {
        $request = new GetOverlaySettingsRequest($request->all());
        if ($request->validate()) {
            $user = User::findOrFail($request->dealer_id);
            return $this->response->item($user, $this->transformer);
        }
        
        return $this->response->errorBadRequest();
    }
    
    public function updateSettings(Request $request)
    {
        $request = new UpdateOverlaySettingsRequest($request->all());

        if ($request->validate()) {

            return $this->response->item($this->imageService->updateOverlaySettings($request->all()), $this->transformer);
        }
       
        return $this->response->errorBadRequest();
    }
    
}
