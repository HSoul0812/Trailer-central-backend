<?php

declare(strict_types=1);

namespace App\Models\Leads;

use App\Support\Traits\TableAware;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int                      $id
 * @property int                      $trailercentral_id
 * @property int                      $inventory_id
 * @property string                   $first_name
 * @property string                   $last_name
 * @property string                   $email_address
 * @property array                    $meta              json data
 * @property DateTimeInterface|string $submitted_at
 * @property DateTimeInterface|string $created_at
 */
class LeadLog extends Model
{
    use HasFactory;
    use TableAware;

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'trailercentral_id',
        'inventory_id',
        'first_name',
        'last_name',
        'email_address',
        'meta',
        'submitted_at',
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
