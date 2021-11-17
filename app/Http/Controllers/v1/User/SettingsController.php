<?php

namespace App\Http\Controllers\v1\User;

use App\Http\Controllers\RestfulControllerV2;
use App\Repositories\User\SettingsRepositoryInterface;
use App\Transformers\User\SettingsTransformer;
use App\Http\Requests\User\Settings\GetSettingsRequest;
use App\Http\Requests\User\Settings\UpdateSettingsRequest;
use App\Http\Requests\User\Settings\EmailSettingsRequest;
use App\Transformers\User\NewsletterTransformer;
use App\Repositories\User\DealerXmlExportRepositoryInterface;
use App\Transformers\User\DealerXmlExportTransformer;
use App\Models\User\User;
use App\Services\CRM\Interactions\InteractionEmailServiceInterface;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;

/**
 * Class SettingsController
 * @package App\Http\Controllers\v1\User
 */
class SettingsController extends RestfulControllerV2
{
    /**
     * @var SettingsRepositoryInterface
     */
    private $repository;

    /**     
     * @var DealerXmlExportRepositoryInterface 
     */
    protected $dealerXmlExportRepo;

    /**     
     * @var EmailSettingsTransformer
     */
    protected $emailSettingsTransformer;

    /**
     * SettingssController constructor.
     * 
     * @param SettingsRepositoryInterface $repository
     * @param DealerXmlExportRepositoryInterface $dealerXmlRepo
     * @param InteractionEmailServiceInterface $interactionEmail
     * @param EmailSettingsTransformer $emailSettings
     */
    public function __construct(
        SettingsRepositoryInterface $repository,
        DealerXmlExportRepositoryInterface $dealerXmlRepo,
        InteractionEmailServiceTransformer $interactionEmail,
        EmailSettingsTransformer $emailSettings
    ) {
        $this->middleware('setDealerIdOnRequest')->only(['index', 'update', 'updateNewsletter', 'getNewsletter', 'updateXmlExport', 'getXmlExport']);
        $this->middleware('setSalesPersonIdOnRequest')->only(['email']);

        $this->repository = $repository;
        $this->dealerXmlExportRepo = $dealerXmlRepo;
        $this->interactionEmail = $interactionEmail;
        $this->emailSettingsTransformer = $emailSettings;
    }
    
    /**
     * @OA\Get(
     *     path="/api/user/settings",
     *     description="Get Dealer Admin Settings",
     * 
     *     @OA\Parameter(
     *         name="setting",
     *         in="query",
     *         description="The specific setting to retrieve; if empty all settings for dealer will be retrieved",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     * 
     *     @OA\Response(
     *         response="200",
     *         description="Returns updated settings",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     */
    public function index(Request $request): Response {
        // Get Settings Request
        $request = new GetSettingsRequest($request->all());
        if ( $request->validate() ) {
            // Return Settings
            return $this->response->collection($this->repository->getAll($request->all()), new SettingsTransformer());
        }

        return $this->response->errorBadRequest();
    }
    
    /**
     * @OA\Put(
     *     path="/api/user/settings",
     *     description="Update Dealer Admin Settings",
     * 
     *     @OA\Response(
     *         response="200",
     *         description="Returns updated settings",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     */
    public function update(Request $request): Response {
        // Update Settings Request
        $request = new UpdateSettingsRequest($request->all());
        if ( $request->validate() ) {
            // Return Settings
            return $this->response->collection($this->repository->createOrUpdate($request->all()), new SettingsTransformer());
        }

        return $this->response->errorBadRequest();
    }

    /**
     * @OA\Get(
     *     path="/api/user/settings/email",
     *     description="Update Dealer Admin Settings",
     * 
     *     @OA\Response(
     *         response="200",
     *         description="Returns updated settings",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     */
    public function email(Request $request): Response {
        // Get Email Settings Request
        $request = new EmailSettingsRequest($request->all());
        if ( $request->validate() ) {
            // Return Settings
            return $this->response->collection(
                $this->interactionEmail->config($request->dealer_id, $request->sales_person_id),
                $this->emailSettingsTransformer
            );
        }

        return $this->response->errorBadRequest();
    }
    
    
    public function updateNewsletter(Request $request): Response {
        $user = User::findOrFail($request->dealer_id);
        $user->newsletter_enabled = $request->newsletter_enabled;
        $user->save();

        return $this->response->item($user, new NewsletterTransformer());
    }
    
    public function getNewsletter(Request $request): Response {                   
        return $this->response->item(User::findOrFail($request->dealer_id), new NewsletterTransformer());
    }
    
    public function getXmlExport(Request $request) : Response {
        return $this->response->item($this->dealerXmlExportRepo->get($request->all()), new DealerXmlExportTransformer);
    }
    
    public function updateXmlExport(Request $request) : Response {
        return $this->response->item($this->dealerXmlExportRepo->updateExport($request->dealer_id, $request->export_status), new DealerXmlExportTransformer);
    }
    
}
