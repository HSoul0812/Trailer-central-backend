<?php

namespace App\Models\Marketing\Craigslist;

use App\Models\User\User;
use App\Models\Marketing\Craigslist\Profile;
use App\Models\Marketing\Craigslist\Session;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\InventoryClapp;
use App\Models\Traits\TableAware;
use App\Traits\Marketing\CraigslistHelper;
use Awobaz\Compoships\Compoships;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Queue
 * 
 * @package App\Models\Marketing\Craigslist
 */
class Queue extends Model
{
    use TableAware, CraigslistHelper, Compoships;

    /**
     * Rental Suffix on Titles
     * 
     * @const string
     */
    const RENTAL_SUFFIX = ' [Rental]';


    // Define Table Name Constant
    const TABLE_NAME = 'clapp_queue';

    /**
     * @var string
     */
    protected $table = self::TABLE_NAME;

    /**
     * @var string
     */
    protected $primaryKey = 'queue_id';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'session_id',
        'parent_id',
        'time',
        'command',
        'parameter',
        'dealer_id',
        'profile_id',
        'inventory_id',
        'status',
        'state',
        'img_state',
        'costs',
        'log'
    ];

    /**
     * Get User
     * 
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dealer_id', 'dealer_id');
    }

    /**
     * Get Profile
     * 
     * @return BelongsTo
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'profile_id', 'id');
    }

    /**
     * Get Inventory
     * 
     * @return BelongsTo
     */
    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'inventory_id', 'inventory_id');
    }

    /**
     * Get Part
     * 
     * @return BelongsTo
     */
    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class, 'id', 'inventory_id');
    }

    /**
     * Get Session
     * 
     * @return BelongsTo
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(Session::class, ['session_id', 'dealer_id', 'profile_id'],
                                ['session_id', 'session_dealer_id', 'session_profile_id']);
    }

    /**
     * Get Inventory Clapp Overrides
     * 
     * @return HasMany
     */
    public function overrides(): HasMany
    {
        return $this->hasMany(InventoryClapp::class, 'inventory_id', 'inventory_id');
    }


    /**
     * Get Parameters
     * 
     * @return \stdclass
     */
    public function getParametersAttribute(): \stdclass {
        return json_decode($this->parameter);
    }

    /**
     * Get Overrides Map
     * 
     * @return array{default-image: string,
     *               body: string,
     *               make: string,
     *               model: string,
     *               postCategory: string}
     */
    public function getOverrideMapAttribute(): array {
        // Create Map
        $map = [
            'default-image' => '',
            'body'          => '',
            'make'          => '',
            'model'         => '',
            'postCategory'  => ''
        ];

        // Insert Overrides
        foreach($this->overrides as $override) {
            $map[$override->field] = $override->value;
        }

        // Return Result
        return $map;
    }


    /**
     * Get Type From Parameters or Profile
     * 
     * @return string
     */
    public function getTypeAttribute(): string {
        return $this->parameters->type ?? $this->profile->profile_type;
    }

    /**
     * Get Post Category From Override or Profile
     * 
     * @return string
     */
    public function getCategoryAttribute(): string {
        // Get Override Post Category
        if(!empty($this->override_map['postCategory'])) {
            return $this->override_map['postCategory'];
        }

        // Return Category ID From Profile
        return $this->profile->category->id;
    }

    /**
     * Get Title From Inventory Or Parts
     * 
     * @return string
     */
    public function getTitleAttribute(): string {
        // Get Parts Title
        $truncate = (int) config('marketing.cl.settings.truncate.title', 70);
        if($this->type === 'parts') {
            $title = $this->part->title;
        } else {
            // Get Override Title
            if(!empty($this->override_map['title'])) {
                $title = $this->override_map['title'];
            } else {
                // Return Inventory Title
                $title = $this->inventory->title;
            }

            // Get Rental
            if(!empty($this->inventory->attributes['is_rental'])) {
                $title .= self::RENTAL_SUFFIX;
                $truncate -= strlen(self::RENTAL_SUFFIX);
            }
        }

        // Truncate Title for CL
        return $this->clTruncate($title, $truncate);
    }

    /**
     * Get Stock From Inventory Or Parts
     * 
     * @return string
     */
    public function getStockAttribute(): string {
        // Get Parts SKU
        if($this->type === 'parts') {
            return $this->part->sku;
        }

        // Return Inventory Stock
        return $this->inventory->stock;
    }

    /**
     * Get Price From Inventory Or Parts
     * 
     * @return string
     */
    public function getPriceAttribute(): string {
        // Get Parts Price
        if($this->type === 'parts') {
            return $this->part->price;
        }

        // Check for Sales Price
        if(!empty($this->inventory->sales_price) && $this->inventory->sales_price !== '0.00') {
            // Return Sales Price Instead
            return $this->inventory->sales_price;
        }

        // Return Inventory Price
        return $this->inventory->price;
    }


    /**
     * Get Description Formats
     * 
     * @return array<string>
     */
    public function getFormatsAttribute(): array {
        // dfbk = description, features, blurb, keywords
        $randomizeFormat = array();

        // Description, Blurb, Keywords
        if($this->profile->format_dbk === 1) {
            $randomizeFormat[] = 'dbk';
        }

        // Features, Blurb, Keywords
        if($this->profile->format_fbk === 1) {
            $randomizeFormat[] = 'fbk';
        }

        // Description, Features, Blurb, Keywords
        if($this->profile->format_dfbk === 1) {
            $randomizeFormat[] = 'dfbk';
        }

        // Description, Blurb, Keywords Only
        if(empty($randomizeFormat)) {
            $randomizeFormat[] = 'dbk';
        }
        return $randomizeFormat;
    }

    /**
     * Get Random Description Format
     * 
     * @return string
     */
    public function getRandomFormatAttribute(): string {
        // Return Random Format
        $random = array_rand($this->formats);
        return $this->formats[$random];
    }

    /**
     * Get Description From Inventory Or Parts
     * 
     * @return string
     */
    public function getDescriptionAttribute(): string {
        // Get Parts Description
        if($this->type === 'parts') {
            $desc = $this->part->description;
        } elseif(!empty($this->override_map['body'])) {
            // Get Override Body
            $desc = $this->override_map['body'];
        } elseif(!empty($this->inventory->description_html)) {
            // Get Override Body
            $desc = $this->clFixHtml($this->inventory->description_html);
        } else {
            $desc = $this->convertMarkdown($this->inventory->description);
        }

        // Return Cleaned Description
        return $desc . "<br /><br />\n\n";
    }

    /**
     * Get Body From Inventory Or Parts
     * 
     * @return string
     */
    public function getBodyAttribute(): string {
        // Return Description
        $description = $desc = $this->description;
        $blurb = $this->profile->body_blurb;
        $keywords = $this->profile->body_keywords;
        $features = '';

        // Return Parts Description
        if($this->type === 'parts') {
            // Return Parts Description
            $description = $desc . $blurb . $keywords;
            if(!empty($this->stock)) {
                $description .= "SKU: " . $this->stock . "<br /><br />\n\n";
            }
        } else {
            // Get Random Format
            $format = $this->random_format;

            // Adjust Format
            if($format == 'dbk') {
                $description = $desc . $blurb . $keywords;
            } elseif($format == 'fbk') {
                $description = $features . $blurb . $keywords;
            } elseif($format == 'dfbk') {
                $description = $desc . $features . $blurb . $keywords;
            }
            if(!empty($this->stock)) {
                $description .= "Stock: " . $this->stock . "<br /><br />\n\n";
            }
        }

        // Finalize Description Adjustments
        $final = trim(str_replace("\r", "", $description));
        return strip_tags($final, $this->clTagsAllowed());
    }


    /**
     * Get Make From Inventory Or Parts
     * 
     * @return string
     */
    public function getMakeAttribute(): string {
        // Get Parts Brand
        if($this->type === 'parts') {
            return $this->part->brand->brand;
        }

        // Get Override Make
        if(!empty($this->override_map['make'])) {
            return $this->override_map['make'];
        }

        // Return Inventory Manufacturer
        return $this->inventory->manufacturer;
    }

    /**
     * Get Model From Inventory Or Parts
     * 
     * @return string
     */
    public function getModelAttribute(): string {
        // Get Parts Model
        if($this->type === 'parts') {
            return '';
        }

        // Get Override Model
        if(!empty($this->override_map['model'])) {
            return $this->override_map['model'];
        }

        // Return Inventory Model
        return $this->inventory->model;
    }

    /**
     * Get Size From Inventory
     * 
     * @return string
     */
    public function getSizeAttribute(): string {
        // Get Parts Sizes
        if($this->type === 'parts') {
            return '';
        }

        // Get Formatted Lengths
        $length = $this->clFormatLengths($this->inventory->length,
                                         $this->inventory->length_inches,
                                         $this->inventory->length_display_mode);
        $width  = $this->clFormatLengths($this->inventory->width,
                                         $this->inventory->width_inches,
                                         $this->inventory->width_display_mode);
        $height = $this->clFormatLengths($this->inventory->height,
                                         $this->inventory->height_inches,
                                         $this->inventory->height_display_mode);

        // Length Exists
        $size = '';
        if(!empty($length)) {
            $size = $length;
            if(empty($width) && empty($height)) {
                return $size . ' X 0" X 0"';
            }
        }

        // Width Exists
        if(!empty($width)) {
            if(empty($length)) {
                $size = '0"';
            }
            $size .= ' X ' . $width;
            if(empty($height)) {
                $size .= ' X 0"';
            }
            return $size;
        }

        // Height Exists
        if(!empty($height)) {
            if(empty($length) && empty($width)) {
                $size = '0" X 0"';
            }
            elseif(empty($width)) {
                $size .= ' X 0"';
            }
            return $size . ' X ' . $height;
        }
        return $size;
    }


    /**
     * Get Images From Inventory Or Parts
     * 
     * @return array<string>
     */
    public function getImagesAttribute(): array {
        // Get Parts Images
        $images = [];
        if($this->type === 'parts') {
            foreach($this->part->images as $image) {
                $images[] = config('app.cdn_url') . $image->image_url;
            }
        } else {
            // Loop Ordered Images
            foreach($this->inventory->orderedImages as $image) {
                $images[] = config('app.cdn_url') . $image->image->filename;
            }
        }

        // Apply an image limit if there is one
        if(!empty($this->profile->image_limit)) {
            $images = array_splice($images, 0, $this->profile->image_limit);
        }

        // Max Limit is Smaller Than Current Amount?
        $maxImages = (int) config('marketing.cl.settings.limits.images', 24);
        if($maxImages < count($images)) {
            $images = array_splice($images, 0, $maxImages);
        }

        // Return Final Images List
        return $images;
    }


    /**
     * Get Current Title
     * 
     * @return string
     */
    public function getCurrentTitleAttribute(): string {
        // Return Title From Inventory
        if(!empty($this->inventory) && !empty($this->inventory->title)) {
            return $this->inventory->title;
        }
        return $this->parameters->title ?? '';
    }

    /**
     * Get Current Stock
     * 
     * @return string
     */
    public function getCurrentStockAttribute(): string {
        // Return Stock From Inventory
        if(!empty($this->inventory) && !empty($this->inventory->stock)) {
            return $this->inventory->stock;
        }
        return $this->parameters->stock ?? '';
    }

    /**
     * Get Current Price
     * 
     * @return float
     */
    public function getCurrentPriceAttribute(): float {
        // Return Price From Inventory
        if(!empty($this->inventory) && !empty($this->inventory->price)) {
            return $this->inventory->price;
        }
        return $this->parameters->price ? floatval($this->parameters->price) : 0;
    }

    /**
     * Get Current Primary Image
     * 
     * @return string
     */
    public function getCurrentImageAttribute(): string {
        // Return Primary Image Inventory
        if(!empty($this->inventory) && !empty($this->inventory->primary_image)) {
            return $this->inventory->primary_image->image->filename;
        }
        return $this->parameters->images[0] ?? '';
    }
}
