<?php

namespace App\Services\Integration\Microsoft;

use App\Exceptions\Integration\Microsoft\MissingAzureIdTokenException;
use App\Models\Integration\Auth\AccessToken;
use App\Services\Integration\Common\DTOs\CommonToken;
use App\Services\Integration\Common\DTOs\EmailToken;
use App\Services\Integration\Common\DTOs\ValidateToken;
use App\Utilities\Fractal\NoDataArraySerializer;
use Illuminate\Support\Facades\Log;
use League\Fractal\Manager;

/**
 * Class OfficeService
 *
 * @package App\Services\Integration\Microsoft
 */
class OfficeService extends AzureService implements OfficeServiceInterface
{
    /**
     * @const Get Office Scope Prefix
     */
    const SCOPE_OFFICE = 'https://outlook.office.com';


    /**
     * Create Microsoft Azure Log
     */
    public function __construct(Manager $fractal)
    {
        // Initialize Services
        $this->fractal = $fractal;
        $this->fractal->setSerializer(new NoDataArraySerializer());

        // Initialize Logger
        $this->log = Log::channel('azure');
    }


    /**
     * Validate Microsoft Azure Access Token Exists and Refresh if Possible
     *
     * @param AccessToken $accessToken
     * @throws MissingAzureIdTokenException
     * @return ValidateToken
     */
    public function validate(AccessToken $accessToken): ValidateToken {
        // ID Token Exists?
        if(empty($accessToken->id_token)) {
            throw new MissingAzureIdTokenException;
        }

        // Initialize Email Token
        $emailToken = new EmailToken();
        $emailToken->fillFromToken($accessToken);

        // Validate By Custom Now
        return $this->validateCustom($emailToken);
    }

    /**
     * Validate Microsoft Azure Access Token Exists and Refresh if Possible
     *
     * @param CommonToken $accessToken
     * @return ValidateToken
     */
    public function validateCustom(CommonToken $accessToken): ValidateToken {
        // Configure Client
        $profile = $this->profile($accessToken);

        // Valid/Expired
        $isValid = ($profile !== null ? true : false);
        $isExpired = ($profile !== null ? $profile->isExpired() : true);

        // Try to Refresh Access Token!
        if($accessToken->refreshToken && (!$isValid || $isExpired)) {
            $refresh = $this->refreshCustom($accessToken);
            if($refresh->exists()) {
                $newProfile = $this->profile($refresh);
                $isValid = ($newProfile !== null ? true : false);
                $isExpired = false;
            }
        }
        if(empty($isValid)) {
            $isExpired = true;
        }

        // Return Payload Results
        return new ValidateToken([
            'new_token' => $refresh ?? null,
            'is_valid' => $isValid,
            'is_expired' => $isExpired,
            'message' => $this->getValidateMessage($isValid, $isExpired)
        ]);
    }


    /**
     * Get Outlook Scopes Including Defaults
     * 
     * @param null|array $scopes
     * @return string
     */
    protected function getScopes(?array $scopes = null): string {
        // Get Scopes
        if(empty($scopes)) {
            if(!empty(config('azure.scopes'))) {
                $scopes = explode(" ", config('azure.scopes'));
            }
        }
        if(empty($scopes)) {
            $scopes = [];
        }

        // Prepend Outlook Scopes
        $final = [];
        foreach($scopes as $scope) {
            $final[] = self::SCOPE_OFFICE . $scope;
        }

        // Return Final Scopes
        return implode(" ", array_merge(self::DEFAULT_SCOPES, $final));
    }

    /**
     * Get Validation Message
     * 
     * @param bool $valid
     * @param bool $expired
     * @return string
     */
    private function getValidateMessage(bool $valid = false, bool $expired = false): string {
        // Return Validation Message
        if(!empty($valid)) {
            if(!empty($expired)) {
                return 'Your Office 365 Authorization has expired! Please try connecting again.';
            } else {
                return 'Your Office 365 Authorization has been validated successfully!';
            }
        }
        return 'Your Office 365 Authorization failed! Please try connecting again.';
    }
}
