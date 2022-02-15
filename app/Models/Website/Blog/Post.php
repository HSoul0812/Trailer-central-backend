<?php

namespace App\Models\Website\Blog;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * Class Blog Post
 *
 * @package App\Models\Website\Blog
 */
class Post extends Model
{    
    const STATUS_PUBLISHED = 'published';
    
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
        'date_modified',
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
        'is_active',
        'form-submit',
        'delete'
    ];

    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'date_created';

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = 'date_modified';

    public static function boot() {
        parent::boot();
    }

    // Make Url Path
    public static function makeUrlPath($url) {
        // Clean Up URL Path
        $url = preg_replace("`\[.*\]`U", "", $url);
        $url = preg_replace('`&(amp;)?#?[a-z0-9]+;`i', '-', $url);
        $url = htmlentities($url, ENT_COMPAT, 'utf-8');
        $url = preg_replace("`&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo);`i", "\\1", $url);
        $url = preg_replace(array("`[^a-z0-9]`i", "`[-]+`"), "-", $url);

        // Insert Hyphens
        return strtolower(trim($url, '-'));
    }
}
