<?php

namespace App\Models\CRM\Interactions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class TextLogFile
 * @package App\Models\CRM\Interactions
 *
 * @property int $id
 * @property int $dealer_texts_log_id
 * @property string $path
 * @property string $type
 * @property \DateTimeInterface $created_at
 * @property \DateTimeInterface $updated_at
 */
class TextLogFile extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dealer_texts_log_files';

    protected $fillable = [
        'dealer_texts_log_id',
        'path',
        'type',
    ];

    /**
     * @return BelongsTo
     */
    public function textLog(): BelongsTo
    {
        return $this->belongsTo(TextLog::class, 'dealer_texts_log_id', 'id');
    }
}
