<?php


namespace App\Transformers\Pos;


use App\Models\Inventory\Inventory;
use App\Models\Parts\Part;
use App\Transformers\Inventory\InventoryTransformerV2;
use App\Transformers\Parts\PartsTransformer;
use League\Fractal\TransformerAbstract;

/**
 * Class PosProductTransformer
 *
 * Given a collection of POS products (parts, inventory), transform each item with the appropriate transformer
 * note: used so that in the collection of ES results, the order (according to ES score) is preserved
 *
 * @package App\Transformers\Pos
 */
class PosProductTransformer extends TransformerAbstract
{
    public function transform($product)
    {
        if ($product instanceof Part) {
            $return = (new PartsTransformer())->transform($product);
            $return['product_type'] = 'part';
            return $return;
        }
        if ($product instanceof Inventory) {
            $return = (new InventoryTransformerV2())->transform($product);
            $return['product_type'] = 'inventory';
            return $return;
        }

        throw new \Exception('Unable to apply transform to product');
    }
}
