<?php

namespace App\Transformers\Parts;

use App\Models\CRM\Dms\PurchaseOrder\PurchaseOrderPart;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;
use App\Models\Parts\Part;
use League\Fractal\Resource\Primitive;

class PartsTransformer extends TransformerAbstract implements PartsTransformerInterface
{
    const CRM_NEW_PO_URL = '/accounting/purchase-order';

    protected $availableIncludes = [
        'purchaseOrders',
        'total_qty',
        'partAttributes'
    ];

    public function transform(Part $part): array
    {
	 return [
             'id' => (int)$part->id,
             'dealer_id' => $part->dealer_id ? (int)$part->dealer_id : null,
             'vendor' => $part->vendor,
             'manufacturer' => $part->manufacturer,
             'brand' => $part->brand,
             'type' => $part->type,
             'category' => $part->category,
             'sku' => $part->sku,
             'stock' => $part->sku,
             'subcategory' => $part->subcategory,
             'title' => $part->title,
             'alternative_part_number' => $part->alternative_part_number,
             'price' => (double)number_format((double)$part->price, 2, '.', ''),
             'dealer_cost' => (double)$part->dealer_cost,
             'latest_cost' => (double)$part->latest_cost,
             'msrp' => (double)$part->msrp,
             'shipping_fee' => (double) $part->shipping_fee,
             'use_handling_fee' => (bool) $part->use_handling_fee,
             'handling_fee' => (double) $part->handling_fee,
             'website_fee' => (double) $part->website_fee,
             'fullfillment_type' => (int) $part->fulfillment_type,
             'weight' => (double)$part->weight,
             'weight_rating' => $part->weight_rating,
             'description' => $part->description,
             'qty' => ($part->qty <= 0) ? 0 : $part->qty,
             'show_on_website' => (bool)$part->show_on_website,
             'is_vehicle_specific' => (bool)$part->is_vehicle_specific,
             'images' => $part->images->pluck('image_url'),
             'vehicle_specific' => $part->vehicleSpecific,
             'video_embed_code' => $part->video_embed_code,
             'stock_min' => $part->stock_min,
             'stock_max' => $part->stock_max,
             'bins' => $part->bins,
             'disabled' => count($part->bins) === 0,
             'new_po_url' => optional($part->user)->getCrmLoginUrl(
                $this->getNewPORoute($part->id),
                true
             ),
             'is_active' => (bool) $part->is_active,
             'taxable' => $part->is_taxable,
         ];
    }

    /**
     * Include part attributes.
     *
     * @param \App\Models\Parts\Part $part
     * @return Collection
     */
    public function includePartAttributes(\App\Models\Parts\Textrail\Part $part): Collection
    {
        return $this->collection(
            $part->partAttributes,
            new PartAttributeTransformer()
        );
    }
    /**
     * Include purchases resource object
     *
     * @param Part $part
     * @return Collection
     */
    public function includePurchaseOrders(Part $part): Collection
    {
        return $this->collection(
            $part->purchaseOrders,
            new PurchaseOrderPartTransformer(), 'data')
            ->setMeta([
                'has_not_completed' => $part->purchaseOrders->filter(static function (
                    PurchaseOrderPart $poPart
                ): bool {
                    return !$poPart->purchaseOrder->isCompleted();
                })->isNotEmpty()
            ]);
    }

    /**
     * Include total qty
     *
     * @param Part $part
     * @return Primitive
     */
    public function includeTotalQty(Part $part): Primitive
    {
        return $this->primitive($part->total_qty);
    }

    private function getNewPORoute(string $partId): string
    {
        return self::CRM_NEW_PO_URL . '?part_id=' . $partId;
    }
}
