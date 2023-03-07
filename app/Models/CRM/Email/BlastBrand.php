<?php

namespace App\Models\CRM\Email;

use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Email Blast Brand
 *
 * @package App\Models\CRM\Email
 *
 * @property int $id
 * @property string $brand
 * @property int $email_blast_id
 */
class BlastBrand extends Model
{
    use TableAware;

    protected $table = 'crm_email_blast_unit_brands';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email_blast_id',
        'brand'
    ];
}
