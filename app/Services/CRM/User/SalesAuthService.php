<?php

namespace App\Services\CRM\User;

use App\Repositories\CRM\User\SalesPersonRepositoryInterface;
use App\Repositories\Integration\Auth\TokenRepositoryInterface;
use App\Service\CRM\User\SalesPersonServiceInterface;
use App\Services\Integration\AuthServiceInterface;
use App\Traits\SmtpHelper;
use App\Transformers\CRM\User\SalesPersonTransformer;
use App\Utilities\Fractal\NoDataArraySerializer;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;

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
        Manager $fractal
    ) {
        $this->salesPersonService = $salesPersonService;
        $this->salesPerson = $salesPersonRepo;
        $this->tokens = $tokens;
        $this->auth = $auth;
        $this->fractal = $fractal;

        $this->fractal->setSerializer(new NoDataArraySerializer());
    }

    /**
     * Show Sales Auth Response
     * 
     * @param array $params
     * @return Fractal
     */
    public function show($params) {
        // Adjust Request
        $params['relation_type'] = 'sales_person';
        $params['relation_id'] = $params['id'];
        unset($params['id']);

        // Get Access Token
        $accessToken = $this->tokens->getRelation($params);

        // Return Response
        return $this->response($accessToken, $params);
    }

    /**
     * Create Sales Person and Auth
     * 
     * @param array $params
     * @return array
     */
    public function create($params) {
        // Create Sales Person
        $salesPerson = $this->salesPersonService->create($params);

        // Adjust Request
        $params['relation_type'] = 'sales_person';
        $params['relation_id'] = $salesPerson->id;

        // Create Access Token
        $accessToken = $this->tokens->create($params);

        // Return Response
        return $this->response($accessToken, $params);
    }

    /**
     * Update Sales Auth
     * 
     * @param array $params
     * @return array
     */
    public function update($params) {
        // Create Sales Person
        $salesPerson = $this->salesPersonService->update($params);

        // Adjust Request
        $params['relation_type'] = 'sales_person';
        $params['relation_id'] = $salesPerson->id;
        unset($params['id']);

        // Create Access Token
        $accessToken = $this->tokens->create($params);

        // Return Response
        return $this->response($accessToken, $params);
    }


    /**
     * Return Response
     * 
     * @param AccessToken $accessToken
     * @param array $params
     * @return array
     */
    public function response($accessToken, $params) {
        // Get Sales Person
        $salesPerson = $this->salesPerson->get([
            'sales_person_id' => $params['relation_id']
        ]);
        $item = new Item($salesPerson, new SalesPersonTransformer(), 'sales_person');
        $this->fractal->parseIncludes('smtp,imap,folders,authTypes');
        $response = $this->fractal->createData($item)->toArray();

        // Return Response
        return $this->auth->response($accessToken, $response);
    }
}
