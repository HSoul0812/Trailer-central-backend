<?php

namespace App\Models\Integration\Collector;

use App\Models\User\NovaUser;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class CollectorAdminNote
 * @package App\Models\Integration\Collector
 *
 * @property int $id
 * @property int $collector_id
 * @property int $user_id
 * @property string $notes
 * @property \DateTimeInterface $created_at
 * @property \DateTimeInterface $updated_at
 *
 */
class CollectorAdminNote extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'collector_admin_note';
    protected $fillable = [
        'collector_id',
        'user_id',
        'note',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Auto-assign admin user on note creation
    public static function boot()
    {
        parent::boot();

        static::creating(function ($note) {
            $note->user_id = auth()->user()->id;
        });
    }

    public function collector(): BelongsTo
    {
        return $this->belongsTo(Collector::class);
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(NovaUser::class);
    }
}
