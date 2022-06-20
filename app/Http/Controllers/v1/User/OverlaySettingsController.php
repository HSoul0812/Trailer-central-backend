<?php

namespace App\Http\Controllers\v1\User;

use App\Http\Controllers\RestfulController;
use App\Repositories\User\UserRepositoryInterface;
use App\Transformers\User\OverlaySettingsTransformer;
use App\Http\Requests\User\GetOverlaySettingsRequest;
use App\Http\Requests\User\UpdateOverlaySettingsRequest;
use Illuminate\Support\Facades\Storage;
use App\Models\User\User;
use Dingo\Api\Http\Request;

class OverlaySettingsController extends RestfulController 
{    
    /**
     * @var UserRepositoryInterface
     */
    protected $userRepo;
    
    /**
     * @var AutoImportSettingsTransformer 
     */
    protected $transformer;
    
    public function __construct(UserRepositoryInterface $userRepo)
    {
        $this->middleware('setDealerIdOnRequest')->only([
            'index', 'updateSettings'
        ]);
        
        $this->userRepo = $userRepo;
        
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
            $logoUrl = null;
            if ($request->overlay_logo) 
            {
                $overlayLogo = $request->overlay_logo;
                $filePath = 'media/'.$overlayLogo->getClientOriginalName();
                Storage::disk('s3')->put($filePath, file_get_contents($overlayLogo));
                $logoUrl = Storage::disk('s3')->url($filePath);
            }
                        
            return $this->response->item($this->userRepo->updateOverlaySettings(
                                                      $request->dealer_id, 
                                                      $request->overlay_enabled, 
                                                      $request->overlay_default, 
                                                      $request->overlay_logo_position, 
                                                      $request->overlay_logo_width, 
                                                      $request->overlay_logo_height, 
                                                      $request->overlay_upper,
                                                      $request->overlay_upper_bg, 
                                                      $request->overlay_upper_alpha, 
                                                      $request->overlay_upper_text, 
                                                      $request->overlay_upper_size, 
                                                      $request->overlay_upper_margin, 
                                                      $request->overlay_lower, 
                                                      $request->overlay_lower_bg,                                                        
                                                      $request->overlay_lower_alpha, 
                                                      $request->overlay_lower_text, 
                                                      $request->overlay_lower_size, 
                                                      $request->overlay_lower_margin, 
                                                      $logoUrl), $this->transformer);
        }
        return $this->response->errorBadRequest();
    }
    
}
