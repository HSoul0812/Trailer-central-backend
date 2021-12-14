<?php

namespace App\Models\Parts;

use App\Support\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;
use App\Models\Parts\Type;

class Category extends Model
{
    protected $table = 'part_categories';

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
       'pivot'
     ];

    public function types()
    {
        return $this->belongsToMany(Type::class, 'part_category_part_type', 'part_type_id', 'part_category_id')->select('id', 'name');
    }

}