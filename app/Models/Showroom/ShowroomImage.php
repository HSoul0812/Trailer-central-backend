<?php

namespace App\Models\Showroom;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ShowroomImage
 * @package App\Models\Showroom
 *
 * @property int $showroom_image_id
 * @property int $showroom_id
 * @property string $src
 * @property bool $is_floorplan
 */
class ShowroomImage extends Model {

    protected $primaryKey = 'showroom_image_id';

    protected $table = 'showroom_image';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'showroom_id',
        'src',
        'is_floorplan'
    ];
}
