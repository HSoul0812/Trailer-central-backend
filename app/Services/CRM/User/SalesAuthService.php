<?php

namespace App\Services\CRM\User;

use App\Http\Requests\CRM\User\AuthorizeSalesAuthRequest;
use App\Models\Integration\Auth\AccessToken;
use App\Repositories\CRM\User\SalesPersonRepositoryInterface;
use App\Repositories\Integration\Auth\TokenRepositoryInterface;
use App\Services\CRM\User\SalesPersonServiceInterface;
use App\Services\CRM\Email\ImapServiceInterface;
use App\Services\Integration\AuthServiceInterface;
use App\Traits\SmtpHelper;
use App\Transformers\CRM\User\SalesPersonTransformer;
use App\Utilities\Fractal\NoDataArraySerializer;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Illuminate\Support\Facades\Log;

/**
 * Class SalesAuthService
 * 
 * @package App\Services\CRM\User
 */
class SalesAuthService implements SalesAuthServiceInterface
{
    use SmtpHelper;

    /**
     * @var SalesPersonService
     */
    protected $salesPersonService;

    /**
     * @var SalesPersonRepository
     */
    protected $salesPerson;

    /**
     * @var TokenRepository
     */
    protected $tokens;

    /**
     * @var AuthServiceInterface
     */
    protected $auth;

    /**
     * @var ImapServiceInterface
     */
    protected $imap;

    /**
     * @var SalesPersonTransformer
     */
    protected $salesTransformer;

    /**
     * @var Manager
     */
    private $fractal;

    /**
     * Construct Sales Auth Service
     */
    public function __construct(
        SalesPersonServiceInterface $salesPersonService,
        SalesPersonRepositoryInterface $salesPersonRepo,
        TokenRepositoryInterface $tokens,
        AuthServiceInterface $auth,
        ImapServiceInterface $imap,
        SalesPersonTransformer $salesTransformer,
        Manager $fractal
    ) {
        $this->salesPersonService = $salesPersonService;
        $this->salesPerson = $salesPersonRepo;
        $this->tokens = $tokens;
        $this->auth = $auth;
        $this->imap = $imap;
        $this->salesTransformer = $salesTransformer;
        $this->fractal = $fractal;

        // Fractal
        $this->fractal = $fractal;
        $this->fractal->setSerializer(new NoDataArraySerializer());

        // Initialize Logger
        $this->log = Log::channel('auth');
    }

    /**
     * Show Sales Auth Response
     * 
     * @param array $params
     * @return array
     */
    public function show(array $params): array {
        // Adjust Request
        $params['relation_type'] = 'sales_person';
        $params['relation_id'] = $params['id'];
        unset($params['id']);

        // Get Access Token
        $accessToken = $this->tokens->getRelation($params);

        // Return Response
        return $this->response($params['relation_id'], $accessToken);
    }

    /**
     * Create Sales Person and Auth
     * 
     * @param array $params
     * @return array
     */
    public function create(array $params): array {
        // Create Sales Person
        $salesPerson = $this->salesPersonService->create($params);

        // Adjust Request
        $params['relation_type'] = 'sales_person';
        $params['relation_id'] = $salesPerson->id;

        // Create Access Token
        $accessToken = null;
        if(!empty($params['token_type'])) {
            $this->log->info('Created sales person #' . $salesPerson->id . ' and updated ' . $params['token_type'] . ' token');
            $accessToken = $this->tokens->create($params);
        } else {
            $this->log->info('Created sales person #' . $salesPerson->id . ' and updated');
        }

        // Return Response
        return $this->response($params['relation_id'], $accessToken);
    }

    /**
     * Update Sales Auth
     * 
     * @param array $params
     * @return array
     */
    public function update(array $params): array {
        // Update Sales Person If Needed
        if(isset($params['email'])) {
            $salesPerson = $this->salesPersonService->update($params);
        } else {
            $salesPerson = $this->salesPerson->get(['sales_person_id' => $params['id']]);
        }

        // Adjust Request
        $params['relation_type'] = 'sales_person';
        $params['relation_id'] = $salesPerson->id;
        unset($params['id']);

        // Create Access Token
        $accessToken = null;
        if(!empty($params['token_type'])) {
            $this->log->info('Updated sales person #' . $salesPerson->id . ' and updated ' . $params['token_type'] . ' token');
            $accessToken = $this->tokens->create($params);
        } else {
            $this->log->info('Updated sales person #' . $salesPerson->id . ' and deleted existing tokens');
            $this->tokens->deleteAll($params['relation_type'], $params['relation_id']);
        }

        // Return Response
        return $this->response($params['relation_id'], $accessToken);
    }

    /**
     * Create Sales Person and Login
     * 
     * @param array $params
     * @return array{data: array<LoginTokenTransformer>,
     *               sales_person: array<SalesPersonTransformer>}
     */
    public function login(array $params): array {
        // Update Sales Person
        if(!empty($params['id'])) {
            $salesPerson = $this->salesPersonService->update($params);
            $this->log->info('Updated sales person #' . $salesPerson->id . ' and return ' . $params['token_type'] . ' login url');
        }
        // Create Sales Person Only If Fields Exist
        elseif(!empty($params['first_name']) && !empty($params['last_name']) && !empty($params['email'])) {
            $salesPerson = $this->salesPersonService->create($params);
            $this->log->info('Created sales person #' . $salesPerson->id . ' and return ' . $params['token_type'] . ' login url');
        }

        // Adjust Request
        $params['relation_type'] = 'sales_person';
        $params['relation_id'] = $salesPerson->id ?? 0;

        // Get Sales Person Response
        $response = [];
        if(!empty($params['relation_id'])) {
            $response = $this->salesResponse($params['relation_id']);
        }

        // Create Login URL
        $login = $this->auth->login($params['token_type'], $params['scopes'], $params['relation_type'], $params['relation_id'], $params['redirect_uri'] ?? null);

        // Return Response
        return array_merge($response, $login);
    }

    /**
     * Authorize Login With Code to Return Access Token
     * 
     * @param AuthorizeSalesAuthRequest $request
     * @param string $tokenType
     * @param string $code
     * @param int $userId
     * @param null|string $state
     * @param null|string $redirectUri
     * @param null|array $scopes
     * @param null|int $salesPersonId
     * @return array{data: array<TokenTransformer>,
     *               sales_person: array<SalesPersonTransformer>}
     */
    public function authorize(AuthorizeSalesAuthRequest $request) {
        // Find Sales Person By State
        if(!empty($request->state)) {
            $stateToken = $this->tokens->getByState($request->state);
            if(!empty($stateToken->relation_id)) {
                $this->log->info('Authorizing token for sales person #' . $stateToken->relation_id);
            }
        }

        // Get Email Token
        $emailToken = $this->auth->code($request->token_type, $request->auth_code, $request->redirect_uri, $request->scopes);
        $this->log->info('Authorized token for email address ' . $emailToken->emailAddress);

        // Initialize Params for Sales Person
        $params = $request->all();
        $params['first_name'] = !empty($params['first_name']) ? $params['first_name'] : $emailToken->firstName;
        $params['last_name']  = !empty($params['last_name']) ? $params['last_name'] : $emailToken->lastName;
        $params['email']      = !empty($params['email']) ? $params['email'] : $emailToken->emailAddress;
        $params['smtp']       = ['email' => $emailToken->emailAddress];
        $params['imap']       = ['email' => $emailToken->emailAddress];

        // Create or Update Sales Person
        if(!empty($stateToken->relation_id) || !empty($request->sales_person_id)) {
            $params['id'] = $request->sales_person_id ?? $stateToken->relation_id;
            unset($params['sales_person_id']);
            $this->log->info('Authorized token and updated sales person #' . $params['id']);
            $salesPerson = $this->salesPersonService->update($params);
        } else {
            $salesPerson = $this->salesPersonService->create($params);
            $this->log->info('Authorized token and created sales person #' . $salesPerson->id);
        }

        // Create Token Params
        $token = $emailToken->toArray($stateToken->id ?? null, $request->token_type,
                'sales_person', $salesPerson->id, $request->state);
        $token['dealer_id'] = $request->dealer_id;

        // Fill Correct Access Token Details
        $accessToken = $this->tokens->create($token);
        if(!empty($stateToken->id) && $accessToken->id !== $stateToken->id) {
            $this->tokens->delete(['id' => $stateToken->id]);
        }

        // Return Response
        return $this->response($salesPerson->id, $accessToken);
    }


    /**
     * Return Response
     * 
     * @param int $salesPersonId
     * @param null|AccessToken $accessToken
     * @return array{sales_person: array<SalesPersonTransformer>,
     *               data: ?array<AccessToken>,
     *               validate: array<ValidateTokenTransformer>}
     */
    public function response(int $salesPersonId, ?AccessToken $accessToken = null): array {
        // Get Sales Person Fractal
        $response = $this->salesResponse($salesPersonId);

        // Set Defaults
        $response['data'] = null;
        $response['validate'] = [
            'is_valid' => false,
            'is_expired' => true,
            'message' => ''
        ];

        // Return Response With Access Token
        if($accessToken !== null) {
            return $this->auth->response($accessToken, $response);
        }
        return $response;
    }


    /**
     * Get Sales Response
     * 
     * @param int $salesPersonId
     * @return array
     */
    private function salesResponse(int $salesPersonId): array {
        // Get Sales Person
        $salesPerson = $this->salesPerson->get([
            'sales_person_id' => $salesPersonId
        ]);

        $item = new Item($salesPerson, $this->salesTransformer, 'sales_person');
        $this->fractal->parseIncludes('smtp,imap,folders,authTypes');
        $response = $this->fractal->createData($item)->toArray();

        // Return Response
        return $this->auth->response($accessToken, $response);
    }
}