<?php

namespace App\Models\User;

use App\Traits\CompactHelper;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;

class Upload extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'upload';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'upload_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'upload_id',
        'created_at',
        'filename',
        'title',
        'hash',
        'last_run_at',
        'last_run_state'
    ];

    public function dealer()
    {
        return $this->hasOne(Dealer::class, 'user_id', 'upload_id');
    }

    public function setFilename($filename = '') {

    }

    public function getIdentifier()
    {
        return CompactHelper::shorten($this->getId());
    }
}
