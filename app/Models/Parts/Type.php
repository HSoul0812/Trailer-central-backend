<?php

namespace App\Models\Parts;

use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    protected $table = 'part_types';

    use TableAware;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [

    ];

    public function parts()
    {
        return $this->hasMany('App\Models\Parts\Part');
    }

    public function __toString() {
        return $this->name;
    }
}
