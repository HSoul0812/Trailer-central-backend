<?php

namespace App\Services\Dispatch\Craigslist\DTOs;

use App\Models\Marketing\Craigslist\Queue;
use App\Traits\WithConstructor;
use App\Traits\WithGetter;

/**
 * Class ClappQueue
 * 
 * @package App\Services\Dispatch\Craigslist\DTOs
 */
class ClappQueue
{
    use WithConstructor, WithGetter;


    /**
     * Default Body
     * 
     * @const string
     */
    const BODY_DEFAULT = 'Thanks for viewing.';


    /**
     * @var string (inventory | parts)
     */
    private $type;

    /**
     * @var string
     */
    private $location;

    /**
     * @var int
     */
    private $postCategory;

    /**
     * @var int
     */
    private $costs;


    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $stock;

    /**
     * @var float
     */
    private $price;

    /**
     * @var string
     */
    private $body;


    /**
     * @var string
     */
    private $make;

    /**
     * @var string
     */
    private $model;

    /**
     * @var string
     */
    private $size;


    /**
     * @var array<string>
     */
    private $images;


    /**
     * @var string
     */
    private $contactName;

    /**
     * @var string
     */
    private $phone;

    /**
     * @var string
     */
    private $postal;


    /**
     * Create ClappQueue From Session/Queue Data
     * 
     * @param Queue $queue
     * @return ClappQueue
     */
    public static function fill(Queue $queue): ClappQueue {
        // Create ClappQueue From Session/Queue
        return new ClappQueue([
            'type' => $queue->type,
            'location' => $queue->profile->location,
            'post_category' => $queue->category,
            'costs' => $queue->profile->costs,
            'title' => $queue->title,
            'stock' => $queue->stock,
            'price' => $queue->price,
            'body' => $queue->body,
            'make' => $queue->make,
            'model' => $queue->model,
            'size' => $queue->size,
            'images' => $queue->images,
            'contact_name' => $queue->profile->contact_name,
            'phone' => $queue->profile->phone,
            'postal' => $queue->profile->postal
        ]);
    }


    /**
     * Get Queue Data JSON Object
     * 
     * @return string json<array{type: string,
     *                           location: string,
     *                           postCategory: int,
     *                           title: string,
     *                           stock: string,
     *                           price: float,
     *                           body: string,
     *                           make: string,
     *                           model: string,
     *                           size: string,
     *                           images: array<string>,
     *                           contact_name: string,
     *                           phone: string,
     *                           postal: string}>
     */
    public function json(): array {
        return $this->clEncodeJson([
            'type' => $this->type,
            'location' => $this->location,
            'postCategory' => $this->postCategory,
            'title' => $this->title,
            'stock' => $this->stock,
            'price' => $this->price,
            'body' => $this->body,
            'make' => $this->make,
            'model' => $this->model,
            'size' => $this->size,
            'contact_name' => $this->contactName,
            'phone' => $this->phone,
            'postal' => $this->postal,
            'images' => $this->images
        ]);
    }


    /**
     * Has Phone?
     * 
     * @return bool
     */
    public function hasPhone() {
        if($this->phone) {
            return true;
        }
        return false;
    }


    /**
     * Get Trimmed Contact Name
     * 
     * @return string
     */
    public function trimmedContactName(): string {
        // Absolute Max Body?
        $truncateContact = (int) config('marketing.cl.settings.truncate.contact', 30);

        // Get Contact Name
        $contactName = str_replace("&", "and", $this->contactName);

        // Trim Contact Name
        return substr($contactName, 0, $truncateContact);
    }

    /**
     * Get Trimmed Body
     * 
     * @return string
     */
    public function trimmedBody(): string {
        // No Body?
        if($this->body) {
            // Absolute Max Body?
            $maxBody = config('marketing.cl.settings.truncate.maxBody', 30000);

            // Trim Post Body
            return substr($this->body, 0, $maxBody);
        }

        // Return Default Body
        return self::BODY_DEFAULT;
    }


    /**
     * Get Trimmed Make
     * 
     * @return string
     */
    public function trimmedMake(): string {
        // Max Make
        $truncate = config('marketing.cl.settings.truncate.make', 32);

        // Return Truncated Make
        return $this->clTruncate($this->make, $truncate);
    }

    /**
     * Get Trimmed Model
     * 
     * @return string
     */
    public function trimmedModel(): string {
        // Max Model
        $truncate = config('marketing.cl.settings.truncate.model', 32);

        // Return Truncated Model
        return $this->clTruncate($this->model, $truncate);
    }
}