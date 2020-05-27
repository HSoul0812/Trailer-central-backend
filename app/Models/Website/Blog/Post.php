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
        'form-submit'
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

    public function create($params = [], $options = []) {
        // Set Published?
        if($params['status'] !== 'private') {
            $params['date_published'] = date('Y-m-d H:i:s');
        }

        // Add URL Path
        $params['url_path'] = $this->makeUrlPath($title);

        // Handle Parent
        return parent::create($params, $options);
    }

    public function update($params = [], $options = []) {
        // Get Existing Item!
        $post = $this->get(['id' => $params['id']]);

        // Set Published?
        if(empty($post['date_published']) && $params['status'] !== 'private') {
            $params['date_published'] = date('Y-m-d H:i:s');
        }

        // Set URL Path?
        if(empty($post['url_path'])) {
            // Add URL Path
            $params['url_path'] = $this->makeUrlPath($title);
        }

        // Handle Parent
        return parent::update($params, $options);
    }

    // Make Url Path
    public function makeUrlPath($url) {
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
