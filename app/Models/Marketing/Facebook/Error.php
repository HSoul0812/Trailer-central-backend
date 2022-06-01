<?php

namespace App\Models\Marketing\Facebook;

use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Error
 * 
 * @package App\Models\Marketing\Facebook\Marketplace
 */
class Error extends Model
{
    use TableAware;


    // Define Table Name Constant
    const TABLE_NAME = 'fbapp_errors';


    /**
     * @const array Account Types
     */
    const ERROR_TYPES = [
        'unknown' => 'Unknown Error',
        'missing-tunnel' => 'Missing Tunnel on Dealer',
        'offline-tunnel' => 'Tunnel Temporarily Offline on Dealer',
        'missing-inventory' => 'Missing Inventory on Integration',
        'login-failed' => 'Failed to Login for Unknown Reason',
        'login-invalid' => 'Invalid Credentials',
        'email-verification' => 'Email Verification',
        'login-approval' => 'Approval Request Submitted',
        'two-factor-auth' => 'Invalid Two-Factor Credentials',
        'two-factor-failed' => 'Two-Factor Failed',
        'account-disabled' => 'Account Disabled',
        'temp-blocked' => 'Temporary Blocked',
        'marketplace-inaccessible' => 'Marketplace Inaccessible',
        'marketplace-blocked' => 'Marketplace Blocked',
        'final-account-review' => 'Marketplace Permanently Blocked',
        'limit-reached' => 'Limit Reached on New Account',
        'failed-post' => 'Inventory Failed to Post',
        'flagged-post' => 'Inventory Post Was Flagged'
    ];

    /**
     * @const Error Type Default
     */
    const ERROR_TYPE_DEFAULT = 'unknown';


    /**
     * @const Expiry Hours
     */
    const EXPIRY_HOURS = [
        'missing-inventory' => 1,
        'email-verification' => 2,
        'missing-tunnel' => 1,
        'two-factor-auth' => 1,
        'two-factor-failed' => 1,
        'marketplace-inaccessible' => 24 * 7,
        'account-disabled' => 24 * 7,
        'marketplace-blocked' => 24 * 7,
        'final-account-review' => 24 * 30 * 12 * 7
    ];

    /**
     * @const Expiry Hours Default
     */
    const EXPIRY_HOURS_DEFAULT = 24;


    /**
     * @const Ignore Expired Status
     */
    const EXPIRED_IGNORE = '0';

    /**
     * @const Follow Expired Status
     */
    const EXPIRED_FOLLOW = '1';

    /**
     * @const Only Get Already Expired Status
     */
    const EXPIRED_ALREADY = '-1';


    /**
     * @var string
     */
    protected $table = self::TABLE_NAME;

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'marketplace_id',
        'inventory_id',
        'action',
        'step',
        'error_type',
        'error_message',
        'dismissed',
        'expires_at'
    ];


    public function getErrorDescAttribute(): string {
        // Get Error Description
        $type = $this->error_type;
        if(!isset(self::ERROR_TYPES[$type])) {
            $type = self::ERROR_TYPE_DEFAULT;
        }

        // Return Error Type Description
        return self::ERROR_TYPES[$type];
    }
}