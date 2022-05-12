<?php

namespace App\Transformers\Inventory;

use App\DTOs\Inventory\TcEsResponseInventoryList;
use JetBrains\PhpStorm\ArrayShape;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Serializer\ArraySerializer;
use League\Fractal\TransformerAbstract;

class InventoryListResponseTransformer extends TransformerAbstract
{
    #[ArrayShape(['inventories' => "array", 'meta' => "array", 'aggregations' => "array", 'limits' => "array"])]
    public function transform(TcEsResponseInventoryList $response): array {
        $manager = new Manager();
        $manager->setSerializer(new ArraySerializer());
        $inventories = $response->inventories->getCollection();
        $resource = new Collection($inventories, new InventoryTransformer());
        $resource->setPaginator(new IlluminatePaginatorAdapter($response->inventories));
        $paginatedArray = $manager->createData($resource)->toArray();
        return [
            'inventories' => $paginatedArray['data'],
            'meta' => $paginatedArray['meta'],
            'aggregations' => $response->aggregations,
        ];
    }
}
