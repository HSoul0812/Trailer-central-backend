<?php

namespace App\Models\Marketing\Facebook;

use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

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

        // Authentication
        'account-disabled' => 'Account Disabled',
        'account-locked' => 'Account Locked',
        'auth-fail' => 'Authentication Failed',
        'email-verification' => 'Email Verification',
        'login-approval' => 'Approval Request Submitted',
        'login-failed' => 'Failed to Login for Unknown Reason',
        'login-invalid' => 'Invalid Credentials',
        'two-factor-auth' => 'Invalid Two-Factor Credentials',
        'two-factor-failed' => 'Two-Factor Failed',

        // Connectivity
        'missing-tunnel' => 'Missing Tunnel on Dealer',
        'offline-tunnel' => 'Tunnel Temporarily Offline on Dealer',
        'page-unavailable' => 'This Page Isn\'t Available',
        'slow-tunnel' => 'Slow Connection',
        'timed-out' => 'Timeout encountered',

        // Inventory
        'missing-inventory' => 'Missing Inventory on Integration',

        // Marketplace
        'final-account-review' => 'Marketplace Permanently Blocked',
        'marketplace-blocked' => 'Marketplace Blocked',
        'marketplace-inaccessible' => 'Marketplace Inaccessible',
        'temp-blocked' => 'Temporary Blocked',

        // Posting
        'failed-post' => 'Inventory Failed to Post',
        'flagged-post' => 'Inventory Post Was Flagged',
        'limit-reached' => 'Limit Reached on New Account',
        'location-invalid' => 'Invalid Location',
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
        'offline-tunnel' => 1,
        'two-factor-auth' => 24,
        'two-factor-failed' => 1,
        'marketplace-inaccessible' => 24,
        'account-locked' => 24 * 7,
        'account-disabled' => 24 * 7,
        'page-unavailable' => 1,
        'marketplace-blocked' => 24 * 7,
        'final-account-review' => 24 * 30 * 12 * 7,
        'timed-out' => 1,
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


    public function getErrorDescAttribute(): string
    {
        // Get Error Description
        $type = $this->error_type;
        if (!isset(self::ERROR_TYPES[$type])) {
            $type = self::ERROR_TYPE_DEFAULT;
        }

        // Return Error Type Description
        return self::ERROR_TYPES[$type];
    }

    /**
     * Create or update Facebook Error
     *
     * @param array $params
     * @return Error
     */
    public static function createOrUpdate($params)
    {
        $error = self::where('marketplace_id', $params['marketplace_id'])
            ->where(function ($query) use ($params) {
                if ($params['inventory_id'] !== null) {
                    $query->where('inventory_id', $params['inventory_id']);
                } else {
                    $query->whereNull('inventory_id');
                }
                return $query;
            })
            ->whereDate('created_at', date('Y-m-d'))
            ->first();

        // If an existing Error is found, update it; otherwise, create a new one
        if ($error) {
            $params['updated_at'] = date('Y-m-d H:i:s');
            $error->update($params);
        } else {
            $error = Error::create($params);
        }

        return $error;
    }
}
