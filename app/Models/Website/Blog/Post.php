<?php

namespace App\Models\Website\Blog;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use Carbon\Carbon;

/**
 * Class Blog Post
 *
 * @package App\Models\Website\Blog
 */
class Post extends Model
{

    use Searchable;

    protected $table = 'website_blog';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'website_id',
        'post_content',
        'meta_keywords',
        'meta_description',
        'title',
        'url_path',
        'entity_config',
        'date_created',
        'date_updated',
        'date_published',
        'status',
        'deleted',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [

    ];

    public static function boot() {
        parent::boot();
    }

    public function searchableAs()
    {
        return env('PARTS_ALGOLIA_INDEX', '');
    }

    public function toSearchableArray()
    {
        $array = $this->toArray();

        return $array;
    }
}
