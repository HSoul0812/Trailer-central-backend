<?php

namespace App\Http\Controllers\v1\User;

use App\Http\Controllers\RestfulController;
use App\Transformers\CRM\Leads\Export\LeadEmailTransformer;
use App\Http\Requests\User\GetAdfSettingsRequest;
use App\Http\Requests\User\UpdateAdfSettingsRequest;
use App\Repositories\CRM\Leads\Export\LeadEmailRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use App\DTO\CRM\Leads\Export\LeadEmail;
use App\Models\User\User;
use Dingo\Api\Http\Request;

class AdfSettingsController extends RestfulController 
{    
    /**
     * @var LeadEmailRepositoryInterface
     */
    protected $leadEmailRepo;
    
    /**
     * @var AutoImportSettingsTransformer 
     */
    protected $transformer;
    
    public function __construct(LeadEmailRepositoryInterface $leadEmailRepo)
    {
        $this->middleware('setDealerIdOnRequest')->only([
            'index', 'updateBulk'
        ]);
        
        $this->leadEmailRepo = $leadEmailRepo;
        
        $this->transformer = new LeadEmailTransformer;
    }
    
    /**
     * Displays a list of all records in the DB.
     * Paginated or not paginated
     */
    public function index(Request $request) 
    {
        $request = new GetAdfSettingsRequest($request->all());
        if ($request->validate()) {
            return $this->response->collection($this->leadEmailRepo->getAll($request->all()), $this->transformer);
        }
        
        return $this->response->errorBadRequest();
    }
    
    public function updateBulk(Request $request)
    {
        $request = new UpdateAdfSettingsRequest($request->all());
        if ($request->validate()) {   
            return $this->response->collection($this->leadEmailRepo->updateBulk($request->getLeadEmails()), $this->transformer);
        }
        return $this->response->errorBadRequest();
    }
    
}
