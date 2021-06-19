<?php

namespace App\Services\CRM\User;

use App\Models\CRM\User\SalesPerson;
use App\Repositories\CRM\User\SalesPersonRepositoryInterface;
use App\Repositories\Integration\Auth\TokenRepositoryInterface;
use App\Services\CRM\Email\DTOs\SmtpConfig;
use App\Services\CRM\Email\DTOs\ImapConfig;
use App\Services\CRM\Email\ImapServiceInterface;
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
     * @var Manager
     */
    private $fractal;

    /**
     * Construct Sales Auth Service
     */
    public function __construct(
        SalesPersonRepositoryInterface $salesPersonRepo,
        TokenRepositoryInterface $tokens,
        AuthServiceInterface $auth,
        ImapServiceInterface $imap,
        Manager $fractal
    ) {
        $this->salesPerson = $salesPersonRepo;
        $this->tokens = $tokens;
        $this->auth = $auth;
        $this->imap = $imap;
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
     * Create Sales Auth
     * 
     * @param array $params
     * @return Fractal
     */
    public function create($params) {
        // Adjust Request
        $params['relation_type'] = 'sales_person';
        $params['relation_id'] = $params['id'];
        unset($params['id']);

        // Create Access Token
        $accessToken = $this->tokens->create($params);

        // Return Response
        return $this->response($accessToken, $params);
    }

    /**
     * Update Sales Auth
     * 
     * @param array $params
     * @return Fractal
     */
    public function update($params) {
        // Adjust Request
        $params['relation_type'] = 'sales_person';
        $params['relation_id'] = $params['id'];
        unset($params['id']);

        // Create Access Token
        $accessToken = $this->tokens->create($params);

        // Return Response
        return $this->response($accessToken, $params);
    }

    /**
     * Validate SMTP/IMAP Details
     * 
     * @param array $params {type: smtp|imap,
     *                       username: string,
     *                       password: string,
     *                       security: string (ssl|tls)
     *                       host: string
     *                       port: int}
     * @return bool
     */
    public function validate(array $params): bool {
        // Initialize Config Params
        $config = [
            'username' => $params['username'],
            'password' => $params['password'],
            'security' => $params['security'],
            'host' => $params['host'],
            'port' => $params['port']
        ];

        // Get Smtp Config Details
        if($params['type'] === SalesPerson::TYPE_SMTP) {
            // Validate SMTP Config
            return $this->validateSmtp(new SmtpConfig($config));
        }
        // Get Imap Config Details
        elseif($params['type'] === SalesPerson::TYPE_IMAP) {
            // Validate IMAP Config
            return $this->imap->validate(new ImapConfig($config));
        }

        // Return Response
        return false;
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
        $this->fractal->parseIncludes('smtp,imap,folders');
        $response = $this->fractal->createData($item)->toArray();

        // Return Response
        return $this->auth->response($accessToken, $response);
    }
}
