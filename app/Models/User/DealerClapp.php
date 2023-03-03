<?php

namespace App\Models\User;

use App\Models\Traits\TableAware;
use App\Models\Marketing\Craigslist\Session;
use App\Models\Marketing\VirtualCard;
use App\Models\Marketing\Craigslist\Account;
use App\Models\Marketing\Craigslist\Profile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class DealerClapp extends Model
{
    use TableAware;


    // Define Table Name Constant
    const TABLE_NAME = 'dealer_clapp';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = self::TABLE_NAME;

    /**
     * @var int
     */
    protected $primaryKey = 'dealer_id';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "dealer_id",
        "slots",
        "chrome_mode",
        "since"
    ];

    /**
     * Get Dealer
     * 
     * @return BelongsTo
     */
    public function dealer(): BelongsTo {
        return $this->belongsTo(User::class, 'dealer_id', 'dealer_id');
    }

    /**
     * Get Active Dealer
     * 
     * @return BelongsTo
     */
    public function activeDealer(): BelongsTo {
        return $this->dealer()->whereNotNull('stripe_id')
                    ->where('state', User::STATUS_ACTIVE);
    }

    /**
     * Get Sessions
     * 
     * @return HasMany
     */
    public function sessions(): HasMany {
        return $this->hasMany(Session::class, 'session_dealer_id', 'dealer_id');
    }

    /**
     * Get Virtual Cards
     * 
     * @return HasMany
     */
    public function virtualCards(): HasMany {
        return $this->hasMany(VirtualCard::class, 'dealer_id', 'dealer_id');
    }

    /**
     * Get CL Accounts
     * 
     * @return HasMany
     */
    public function accounts(): HasMany {
        return $this->hasMany(Account::class, 'dealer_id', 'dealer_id');
    }

    /**
     * Get Profiles
     * 
     * @return HasMany
     */
    public function profiles(): HasMany {
        return $this->hasMany(Profile::class, 'dealer_id', 'dealer_id');
    }

    /**
     * Get Next Session Date
     * 
     * @return string
     */
    public function getNextSessionAttribute(): string {
        // Get Session
        $session = $this->sessions()->whereNotNull('session_scheduled')
                        ->where('session_scheduled', '<=', DB::raw('NOW()'))
                        ->where('session_slot_id', '=', 99)
                        ->where('status', '<>', 'done')
                        ->where('state', '<>', 'error')
                        ->where(function (Builder $query) {
                            $query->where('status', '=', 'scheduled')
                                  ->orWhere('status', '=', 'new');
                        })->first();

        // Return Session Scheduled
        return $session->session_scheduled ?? '';
    }
}
