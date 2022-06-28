<?php

namespace App\Services\CRM\Leads\Import;

use App\Exceptions\CRM\Leads\Import\InvalidAdfDealerIdException;
use App\Exceptions\CRM\Leads\Import\MissingAdfEmailAccessTokenException;
use App\Models\Integration\Auth\AccessToken;
use App\Repositories\Integration\Auth\TokenRepositoryInterface;
use App\Repositories\System\EmailRepositoryInterface;
use App\Services\Integration\Google\GoogleServiceInterface;
use Carbon\CarbonImmutable;

/**
 * Class ImportService
 * @package App\Services\CRM\Leads\Import
 */
class ImportService implements ImportServiceInterface
{
    /**
     * @var AbstractImportService[]
     */
    private $services;

    /**
     * @var EmailRepositoryInterface
     */
    private $emails;

    /**
     * @var GoogleServiceInterface
     */
    private $google;

    /**
     * @var TokenRepositoryInterface
     */
    private $tokens;

    public function __construct(
        EmailRepositoryInterface $emails,
        GoogleServiceInterface $google,
        TokenRepositoryInterface $tokens,
        ADFService $adfService,
        HtmlService $htmlService
    ) {
        $this->emails = $emails;
        $this->google = $google;
        $this->tokens = $tokens;

        $this->services = [$adfService, $htmlService];
    }

    public function import(): int
    {
        $accessToken = $this->getAccessToken();
        $inbox = config('adf.imports.gmail.inbox');
        $messages = $this->gmail->messages($accessToken, $inbox);

        // Checking Each Message
        $total = 0;
        foreach($messages as $mailId) {
            // Get Message Overview
            $email = $this->gmail->message($mailId);

            // Find Exceptions
            try {
                // Validate ADF
                $crawler = $this->validateAdf($email->getBody());

                // Find Dealer ID
                $dealerId = str_replace('@' . config('adf.imports.gmail.domain'), '', $email->getToEmail());
                try {
                    $dealer = $this->dealers->get(['dealer_id' => $dealerId]);
                } catch (\Exception $e) {
                    throw new InvalidAdfDealerIdException;
                }
            } catch (\Exception $e) {
            }
        }

        return 1;
    }

    /**
     * Get Access Token for ADF
     *
     * @throws MissingAdfEmailAccessTokenException
     * @return AccessToken
     */
    private function getAccessToken(): AccessToken
    {
        // Get Email
        $email = config('adf.imports.gmail.email');

        // Get System Email With Access Token
        $systemEmail = $this->emails->find(['email' => $email]);

        // No Access Token?
        if(empty($systemEmail->googleToken)) {
            throw new MissingAdfEmailAccessTokenException;
        }

        // Refresh Token
        $accessToken = $systemEmail->googleToken;
        $validate = $this->google->validate($accessToken);
        if(!empty($validate->newToken)) {
            // Refresh Access Token
            $time = CarbonImmutable::now();
            $accessToken = $this->tokens->update([
                'id' => $accessToken->id,
                'access_token' => $validate->newToken['access_token'],
                'id_token' => $validate->newToken['id_token'],
                'expires_in' => $validate->newToken['expires_in'],
                'expires_at' => $time->addSeconds($validate->newToken['expires_in'])->toDateTimeString(),
                'issued_at' => $time->toDateTimeString()
            ]);
        }

        // Return Access Token for Google
        return $accessToken;
    }
}
