<?php

namespace App\Models\Parts;

use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class BinQuantity
 * @package App\Models\Parts
 *
 * @property int $id
 * @property int $part_id
 * @property int $bin_id
 * @property int $qty
 * @property \DateTimeInterface $created_at
 * @property \DateTimeInterface $updated_at
 *
 * @property Part $part
 */
class BinQuantity extends Model {

    use TableAware;

    protected $table = 'part_bin_qty';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'part_id',
        'bin_id',
        'qty'
    ];

    protected $touches = ['part'];

    /**
     * @return HasOne
     */
    public function bin(): HasOne
    {
        return $this->hasOne('App\Models\Parts\Bin', 'id', 'bin_id');
    }

    /**
     * @return BelongsTo
     */
    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }
}
