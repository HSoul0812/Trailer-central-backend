<?php

declare(strict_types=1);

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int                      $id
 * @property string                   $name
 * @property string                   $status      ['working'|'finished'|'failed']
 * @property array                    $meta        json data
 * @property DateTimeInterface|string $created_at
 * @property DateTimeInterface|string $updated_at
 * @property DateTimeInterface|string $finished_at
 */
class SyncProcess extends Model
{
    use HasFactory;

    public const STATUS_WORKING = 'working';
    public const STATUS_FINISHED = 'finished';
    public const STATUS_FAILED = 'failed';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'finished_at',
        'status',
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
