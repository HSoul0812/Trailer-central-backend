<?php

namespace App\Http\Requests\Inventory;

use App\Http\Requests\Request;
use App\Transformers\TransformerInterface;

/**
 * Class SaveInventoryAttributeRequest
 *
 * @package App\Http\Requests\Inventory
 */
class SaveInventoryAttributeRequest extends Request
{
    protected $rules = [
        'inventoryId' => [
            'integer',
            'required',
            'exists:inventory,inventory_id',
        ],
        'attributes' => [
            'array',
            'min:1',
        ],
        'attributes.*.id' => [
            'integer',
            'required',
        ],
        'attributes.*.value' => [
            'string',
            'required',
            'min:1',
        ],
    ];

    /**
     * @var TransformerInterface
     */
    private $transformer;

    /**
     * {@inheritDoc}
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function all($keys = null)
    {
        return $this->transformer->transform(parent::all($keys));
    }

    /**
     * @param TransformerInterface $transformer
     */
    public function setTransformer(TransformerInterface $transformer): void
    {
        $this->transformer = $transformer;
    }
}
