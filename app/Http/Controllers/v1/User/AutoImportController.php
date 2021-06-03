<?php

namespace App\Http\Controllers\v1\User;

use App\Http\Controllers\RestfulController;
use App\Repositories\User\UserRepositoryInterface;
use App\Transformers\User\AutoImportSettingsTransformer;
use App\Http\Requests\User\GetAutoImportSettingsRequest;
use App\Http\Requests\User\UpdateAutoImportSettingsRequest;
use App\Models\User\User;
use Dingo\Api\Http\Request;

class AutoImportController extends RestfulController 
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
        
        $this->transformer = new AutoImportSettingsTransformer;
    }
    
    /**
     * Displays a list of all records in the DB.
     * Paginated or not paginated
     */
    public function index(Request $request) 
    {
        $request = new GetAutoImportSettingsRequest($request->all());
        if ($request->validate()) {
            $user = User::findOrFail($request->dealer_id);
            return $this->response->item($user, $this->transformer);
        }
        
        return $this->response->errorBadRequest();
    }
    
    public function updateSettings(Request $request)
    {
        $request = new UpdateAutoImportSettingsRequest($request->all());
        if ($request->validate()) {
            return $this->response->item($this->userRepo->updateAutoImportSettings(
                                                      $request->dealer_id, 
                                                      $request->default_description, 
                                                      $request->use_description_in_feed, 
                                                      $request->auto_import_hide, 
                                                      $request->import_config, 
                                                      $request->auto_msrp, 
                                                      $request->auto_msrp_percent), $this->transformer);
        }
        return $this->response->errorBadRequest();
    }
    
}
