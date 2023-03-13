<?php

namespace App\Models\Feed\Uploads;

use Illuminate\Database\Eloquent\Model;

/**
 * Class FeedApiUpload
 *
 * For feed data uploaded via api
 *
 * @package App\Models\Feed\Uploads
 */
class FeedApiUpload extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'feed_api_uploads';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['code', 'key', 'type', 'data'];

    /**
     * @var string[]
     */
    protected $dates = [
        'created_at',
        'updated_at'
    ];
}
