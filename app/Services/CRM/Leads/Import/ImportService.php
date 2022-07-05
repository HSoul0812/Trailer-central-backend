<?php

namespace App\Services\CRM\Leads\Import;

use App\Exceptions\CRM\Leads\Import\InvalidDealerIdException;
use App\Exceptions\CRM\Leads\Import\InvalidImportFormatException;
use App\Exceptions\CRM\Leads\Import\MissingAdfEmailAccessTokenException;
use App\Models\Integration\Auth\AccessToken;
use App\Repositories\Integration\Auth\TokenRepositoryInterface;
use App\Repositories\System\EmailRepositoryInterface;
use App\Repositories\User\UserRepositoryInterface;
use App\Services\Integration\Common\DTOs\ParsedEmail;
use App\Services\Integration\Google\GmailServiceInterface;
use App\Services\Integration\Google\GoogleServiceInterface;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Log;

/**
 * Class ImportService
 * @package App\Services\CRM\Leads\Import
 */
class ImportService implements ImportServiceInterface
{
    /**
     * @var ImportTypeInterface[]
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

    /**
     * @var GmailServiceInterface
     */
    protected $gmail;

    /**
     * @var UserRepositoryInterface
     */
    protected $dealers;

    public function __construct(
        EmailRepositoryInterface $emails,
        GoogleServiceInterface $google,
        TokenRepositoryInterface $tokens,
        UserRepositoryInterface $dealers,
        GmailServiceInterface $gmail,
        ADFService $adfService,
        HtmlService $htmlService
    ) {
        $this->emails = $emails;
        $this->google = $google;
        $this->tokens = $tokens;
        $this->gmail = $gmail;
        $this->dealers = $dealers;

        $this->services = [$adfService, $htmlService];
    }

    public function import(): int
    {
        $accessToken = $this->getAccessToken();
        $inbox = config('adf.imports.gmail.inbox');
        $messages = $this->gmail->messages($accessToken, $inbox);

        print_r($messages);exit();

        // Checking Each Message
        $total = 0;
        foreach($messages as $mailId) {
            /** @var ParsedEmail $email */
            $email = $this->gmail->message($mailId);

            try {
                $neededService = null;

                foreach ($this->services as $service) {
                    if ($service->isSatisfiedBy($email)) {
                        $neededService = $service;
                        break;
                    }
                }

                if (!$neededService instanceof ImportTypeInterface) {
                    throw new InvalidImportFormatException();
                }

                // Find Dealer ID
                $dealerId = str_replace('@' . config('adf.imports.gmail.domain'), '', $email->getToEmail());
                try {
                    $dealer = $this->dealers->get(['dealer_id' => $dealerId]);
                } catch (\Exception $e) {
                    throw new InvalidDealerIdException;
                }

                $result = $neededService->import($dealer, $email);

                if (!empty($result)) {
                    Log::info('Imported ADF Lead ' . $result->identifier . ' and Moved to Processed');
                    $this->gmail->move($accessToken, $mailId, [config('adf.imports.gmail.processed')], [$inbox]);
                    $total++;
                }

            } catch(InvalidDealerIdException $e) {
                if(!empty($dealerId) && is_numeric($dealerId)) {
                    $this->gmail->move($accessToken, $mailId, [config('adf.imports.gmail.unmapped')], [$inbox]);
                } else {
                    $this->gmail->move($accessToken, $mailId, [config('adf.imports.gmail.invalid')], [$inbox]);
                }
                Log::error("Exception returned on Import Message #{$mailId} {$e->getMessage()}: {$e->getTraceAsString()}");
            } catch(InvalidImportFormatException $e) {
                $this->gmail->move($accessToken, $mailId, [config('adf.imports.gmail.invalid')], [$inbox]);
                Log::error("Exception returned on Import Message #{$mailId} {$e->getMessage()}: {$e->getTraceAsString()}");
            } catch(\Exception $e) {
                Log::error("Exception returned on Import Message #{$mailId} {$e->getMessage()}: {$e->getTraceAsString()}");
            }
        }

        return $total;
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
