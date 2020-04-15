<?php


namespace App\Models\Feed\Uploads;


use Illuminate\Database\Eloquent\Model;

/**
 * Class FeedApiUpload
 *
 * For feed data uplaoded via api
 *
 * @package App\Models\Feed\Uploads
 */
class FeedApiUpload extends Model
{
    protected $table = 'feed_api_uploads';

    protected $fillable = ['code', 'type', 'data'];
}
