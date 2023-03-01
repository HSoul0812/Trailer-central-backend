<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class DealerClapp extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "dealer_clapp";

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
        return $this->hasMany(Session::class, 'dealer_id', 'session_dealer_id');
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
                        });

        // Return Session Scheduled
        return $session->session_scheduled;
    }
}
