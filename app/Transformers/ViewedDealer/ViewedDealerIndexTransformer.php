<?php

namespace App\Transformers\ViewedDealer;

use App\Models\Dealer\ViewedDealer;
use App\Services\Inventory\InventoryServiceInterface;
use App\Transformers\Inventory\TcApiResponseInventoryTransformer;
use League\Fractal\TransformerAbstract;
use Str;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ViewedDealerIndexTransformer extends TransformerAbstract
{
    public function __construct(
        private InventoryServiceInterface $inventoryService,
        private TcApiResponseInventoryTransformer $transformer,
    ) {
    }

    public function transform(ViewedDealer $viewedDealer): array
    {
        return [
            'id' => $viewedDealer->id,
            'name' => $viewedDealer->name,
            'dealer_id' => $viewedDealer->dealer_id,
            'inventory_id' => $viewedDealer->inventory_id,
            'inventory' => $this->getInventory($viewedDealer->inventory_id),
            'created_at' => $viewedDealer->created_at,
            'updated_at' => $viewedDealer->updated_at,
        ];
    }

    private function getInventory(int $inventoryId): ?array
    {
        try {
            $tcInventory = $this->inventoryService->show($inventoryId);
        } catch (HttpException $e) {
            $isNotFoundError = Str::of($e->getMessage())->contains('The selected id is invalid.');

            if ($isNotFoundError) {
                return null;
            }

            throw $e;
        }

        return $this->transformer->transform($tcInventory);
    }
}
