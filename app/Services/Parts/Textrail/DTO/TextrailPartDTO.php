<?php

namespace App\Services\Parts\Textrail\DTO;

use App\Contracts\Support\DTO;
use App\Traits\WithFactory;
use App\Traits\WithGetter;

/**
 * Class SimpleData
 *
 * @package App\Services\Common\DTOs
 */
class TextrailPartDTO implements DTO
{
    use WithFactory;
    use WithGetter;

    /** @var int */
    private $id;

    /** @var int */
    public $manufacturer_id;

    /** @var int */
    public $brand_id;

    /** @var int */
    public $type_id;

    /** @var int */
    public $category_id;

    /** @var string */
    private $sku;

    /** @var string */
    private $title;

    /** @var int */
    private $price;

    /** @var int */
    private $weight;

    /** @var string */
    private $description;

    /** @var int */
    private $show_on_website;

    /** @var array */
    private $images = [];

    /** @var int */
    public $qty = 0;

    /** @var array */
    public $custom_attributes = [];

    public static function result(array $properties): self
    {
        return self::from($properties);
    }

    public function asArray(): array
    {
        return [
            'id' => $this->id,
            'manufacturer_id' => $this->manufacturer_id,
            'brand_id' => $this->brand_id,
            'type_id' => $this->type_id,
            'category_id' => $this->category_id,
            'sku' => $this->sku,
            'title' => $this->title,
            'price' => $this->price,
            'weight' => $this->weight ?? '',
            'description' => $this->description,
            'show_on_website' => $this->show_on_website,
            'images' => $this->images,
            'qty' => $this->qty,
            'custom_attributes' => $this->custom_attributes,
        ];
    }
}
