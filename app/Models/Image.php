<?php

namespace App\Models;

use App\Traits\CompactHelper;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'image';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'image_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "image_id",
        "filename",
        "filename_noverlay",
        "created_at",
        "hash",
        "program"
    ];

    public function getIdentifier() {
        return CompactHelper::shorten($this->getId());
    }
}
