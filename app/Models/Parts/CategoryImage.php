<?php

declare(strict_types=1);

namespace App\Models\Parts;

use Illuminate\Database\Eloquent\Model;

class CategoryImage extends Model
{
    protected $table = 'category_images';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'category_id',
        'image_url',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
    ];
}
