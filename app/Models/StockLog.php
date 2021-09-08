<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Traits\TableAware;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int                      $id
 * @property int                      $record_id
 * @property string                   $event        ['created'|'updated'|'price-changed']
 * @property string                   $status       ['available'|'sold']
 * @property string                   $vin
 * @property string                   $brand
 * @property string                   $manufacturer
 * @property numeric                  $price
 * @property array                    $meta         json data
 * @property DateTimeInterface|string $created_at
 */
class StockLog extends Model
{
    use HasFactory;
    use TableAware;

    public const STATUS_AVAILABLE = 'available';
    public const STATUS_SOLD = 'sold';

    public const EVENT_CREATED = 'created';
    public const EVENT_UPDATED = 'updated';
    public const EVENT_PRICE_CHANGED = 'price-changed';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'record_id',
        'event',
        'status',
        'vin',
        'brand',
        'manufacturer',
        'price',
        'meta',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var string[]
     */
    protected $casts = [
        'meta' => 'array',
    ];
}
