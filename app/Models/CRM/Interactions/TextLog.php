<?php

namespace App\Models\CRM\Interactions;

use App\Models\CRM\Leads\Lead;
use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Class TextLog
 * @package App\Models\CRM\Interactions
 *
 * @property integer $id
 * @property integer $lead_id
 * @property string $log_message
 * @property string $from_number
 * @property string $to_number
 * @property \DateTimeInterface $date_sent
 * @property \DateTimeInterface $created_at
 * @property \DateTimeInterface $updated_at
 *
 * @property Lead $lead
 * @property InteractionMessage $interactionMessage
 * @property Collection<TextLogFile> $files
 */
class TextLog extends Model
{
    use TableAware;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dealer_texts_log';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'date_sent';

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = NULL;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'lead_id',
        'log_message',
        'from_number',
        'to_number',
        'date_sent'
    ];

    /**
     * @return BelongsTo
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'lead_id', 'identifier');
    }

    /**
     * @return MorphOne
     */
    public function interactionMessage(): MorphOne
    {
        return $this->morphOne(InteractionMessage::class, 'interactionMessage', 'tb_name', 'tb_primary_id');
    }

    /**
     * @return HasMany
     */
    public function files(): HasMany
    {
        return $this->hasMany(TextLogFile::class, 'dealer_texts_log_id', 'id');
    }
}
