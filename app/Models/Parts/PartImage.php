<?php

namespace App\Models\Parts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PartImage extends Model {

    protected $table = 'part_images';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'part_id',
        'image_url',
        'position'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [

    ];

    public function __toString() {
        return $this->image_url;
    }

    protected static function boot()
    {
        parent::boot();

        // queries should order by `position` by default
        static::addGlobalScope('position', function (Builder $builder) {
            $builder->orderBy('position', 'asc');
        });
    }
}
