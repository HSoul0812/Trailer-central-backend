<?php

namespace App\Repositories\Parts;

use App\Events\Parts\PartQtyUpdated;
use App\Repositories\Repository;
use App\Models\Parts\Part;
use App\Models\Parts\PartImage;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Storage;
use App\Models\Parts\VehicleSpecific;
use Illuminate\Support\Facades\DB;
use App\Models\Parts\BinQuantity;
use App\Exceptions\ImageNotDownloadedException;
use App\Repositories\Traits\SortTrait;

/**
 *
 * @author Eczek
 */
class PartRepository implements PartRepositoryInterface {

    use SortTrait;

    private $sortOrders = [
        'title' => [
            'field' => 'title',
            'direction' => 'DESC'
        ],
        '-title' => [
            'field' => 'title',
            'direction' => 'ASC'
        ],
        'price' => [
            'field' => 'price',
            'direction' => 'DESC'
        ],
        '-price' => [
            'field' => 'price',
            'direction' => 'ASC'
        ],
        'sku' => [
            'field' => 'sku',
            'direction' => 'DESC'
        ],
        '-sku' => [
            'field' => 'sku',
            'direction' => 'ASC'
        ],
        'dealer_cost' => [
            'field' => 'dealer_cost',
            'direction' => 'DESC'
        ],
        '-dealer_cost' => [
            'field' => 'dealer_cost',
            'direction' => 'ASC'
        ],
        'msrp' => [
            'field' => 'msrp',
            'direction' => 'DESC'
        ],
        '-msrp' => [
            'field' => 'msrp',
            'direction' => 'ASC'
        ],
        'subcategory' => [
            'field' => 'subcategory',
            'direction' => 'DESC'
        ],
        '-subcategory' => [
            'field' => 'subcategory',
            'direction' => 'ASC'
        ],
        'created_at' => [
            'field' => 'created_at',
            'direction' => 'DESC'
        ],
        '-created_at' => [
            'field' => 'created_at',
            'direction' => 'ASC'
        ],
        'stock' => [
            'field' => 'stock',
            'direction' => 'DESC'
        ],
        '-stock' => [
            'field' => 'stock',
            'direction' => 'ASC'
        ]
    ];

    private $indexKeywordFields = [
        'subcategory' => 'subcategory.keyword',
        'title' => 'title.keyword',
        'sku' => 'sku.keyword',
        'price' => 'price.keyword',
    ];

    public function create($params) {
        DB::beginTransaction();

        try {
            $part = $this->createPart($params);

            if (isset($params['is_vehicle_specific']) && $params['is_vehicle_specific']) {

                VehicleSpecific::create([
                    'make' => $params['vehicle_make'],
                    'model' => $params['vehicle_model'],
                    'year_from' => $params['vehicle_year_from'],
                    'year_to' => $params['vehicle_year_to'],
                    'part_id' => $part->id
                ]);

            }

            if (isset($params['images'])) {
                foreach ($params['images'] as $image) {
                    try {
                        $this->storeImage($part->id, $image);
                    } catch (ImageNotDownloadedException $ex) {

                    }

                }
            }

            if (isset($params['bins'])) {
                foreach ($params['bins'] as $bin) {
                    $binQty = $this->createBinQuantity([
                        'part_id' => $part->id,
                        'bin_id' => $bin['bin_id'],
                        'qty' => $bin['quantity']
                    ]);
                }
            }

             DB::commit();
        } catch (ImageNotDownloadedException $ex) {
            DB::rollBack();
            throw new ImageNotDownloadedException($ex->getMessage());
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new \Exception($ex->getMessage());
        }


       return $part;
    }

    public function createOrUpdate($params) {
        // Part is unique if the SKU is unique for the dealer id
        $part = Part::where('sku', $params['sku'])->where('dealer_id', $params['dealer_id'])->first();

        if ($part) {
            $params['id'] = $part->id;
            return $this->update($params);
        }

        return $this->create($params);
    }

    public function delete($params) {
        $part = Part::findOrFail($params['id']);
        return $part->delete();
    }

    public function get($params) {
        return Part::findOrFail($params['id'])->load('bins.bin');
    }

    public function getDealerSku($dealerId, $sku) {
        return Part::where('sku', $sku)->where('dealer_id', $dealerId)->first();
    }

    public function getBySku($sku) {
        return Part::where('sku', $sku)->first();
    }

    public function getAll($params)
    {
        /** @var Builder $query */
        $query = Part::where('id', '>', 0);

        if (!isset($params['per_page'])) {
            $params['per_page'] = 15;
        }

        if (isset($params['dealer_id'])) {
             $query = $query->whereIn('dealer_id', $params['dealer_id']);
        }

        if (isset($params['type_id'])) {
            if (isset($params['type_id']['eq'])) {
                $query = $query->whereIn('type_id', $params['type_id']['eq']);
            }

            if (isset($params['type_id']['neq'])) {
                $query = $query->whereNotIn('type_id', $params['type_id']['neq']);
            }

            if (!isset($params['type_id']['eq']) && !isset($params['type_id']['neq'])) {
                $query = $query->whereIn('type_id', $params['type_id']);
            }
        }

        if (isset($params['category_id'])) {
            if (isset($params['category_id']['eq'])) {
                $query = $query->whereIn('category_id', $params['category_id']['eq']);
            }

            if (isset($params['category_id']['neq'])) {
                $query = $query->whereNotIn('category_id', $params['category_id']['neq']);
            }

            if (!isset($params['category_id']['eq']) && !isset($params['category_id']['neq'])) {
                $query = $query->whereIn('category_id', $params['category_id']);
            }
        }

        if (isset($params['manufacturer_id'])) {
            $query = $query->whereIn('manufacturer_id', $params['manufacturer_id']);
        }

        if (isset($params['brand_id'])) {
            if (isset($params['brand_id']['eq'])) {
                $query = $query->whereIn('brand_id', $params['brand_id']['eq']);
            }

            if (isset($params['brand_id']['neq'])) {
                $query = $query->whereNotIn('brand_id', $params['brand_id']['neq']);
            }

            if (!isset($params['brand_id']['eq']) && !isset($params['brand_id']['neq'])) {
                $query = $query->whereIn('brand_id', $params['brand_id']);
            }
        }

        if (isset($params['show_on_website'])) {
            $query = $query->where('show_on_website', $params['show_on_website']);
        }

        if (isset($params['id'])) {
             $query = $query->whereIn('id', $params['id']);
        }

        if (isset($params['subcategory'])) {
            if (isset($params['subcategory']['eq'])) {
                $query = $query->whereIn('subcategory', $params['subcategory']['eq']);
            }

            if (isset($params['subcategory']['neq'])) {
                $query = $query->whereNotIn('subcategory', $params['subcategory']['neq']);
            }

            if (!isset($params['subcategory']['eq']) && !isset($params['subcategory']['neq'])) {
                $query = $query->where('subcategory', 'LIKE', '%' . $params['subcategory'] . '%');
            }
        }
        
        if (isset($params['sku'])) {
            if (isset($params['sku']['contain'])) {
                $query = $query->where(function ($query) use ($params) {
                    foreach ($params['sku']['contain'] as $sku) {
                        $query->orWhere('sku', 'LIKE', '%' . $sku . '%');
                    }
                });
            }

            if (isset($params['sku']['dncontain'])) {
                $query = $query->where(function ($query) use ($params) {
                    foreach ($params['sku']['dncontain'] as $sku) {
                        $query->where('sku', 'NOT LIKE', '%' . $sku . '%');
                    }
                });
            }

            if (!isset($params['sku']['contain']) && !isset($params['sku']['dncontain'])) {
                $query = $query->where('sku', 'LIKE', '%'.$params['sku'].'%');
            }
        }

        if (isset($params['show_on_website'])) {
           $query = $query->where('show_on_website', $params['show_on_website']);
        }

        if (isset($params['price_min']) && isset($params['price_max'])) {
            $query = $query->whereBetween('price', [$params['price_min'], $params['price_max']]);
        } else if (isset($params['price'])) {
            if (isset($params['price']['gt'])) {
                $query = $query->where('price', '>=', max($params['price']['gt']));
            }

            if (isset($params['price']['lt'])) {
                $query = $query->where('price', '<=', min($params['price']['lt']));
            }

            if (!isset($params['price']['gt']) && !isset($params['price']['lt'])) {
                $query = $query->where('price', $params['price']);
            }
        }

        if (isset($params['with_cost'])) {
            if (empty($params['with_cost'])) {
                $query = $query->where(function($q) {
                    $q->whereNull('dealer_cost')->orWhere('dealer_cost', '=', 0);
                });
            } else {
                $query = $query->where('dealer_cost', '>', 0);
            }
        }

        if (isset($params['vendor_id']) && !empty($params['vendor_id'])) {
            $query = $query->whereHas('vendor', function($query) use($params) {
                $query->where('id', '=', $params['vendor_id']);
            });
        }

        if (isset($params['in_stock'])) {
            if (empty($params['in_stock'])) {
                $query = $query->where(function($query) {
                    $query->whereHas('bins', function($query) {
                        $query->select(DB::raw('sum(qty) as total_qty'))
                            ->groupBy('part_id')
                            ->havingRaw('total_qty <= 0');
                    })
                    ->orDoesntHave('bins');
                });
            } else {
                $query = $query->whereHas('bins', function($query) {
                    $query->select(DB::raw('sum(qty) as total_qty'))
                        ->groupBy('part_id')
                        ->havingRaw('total_qty > 0');
                });
            }
        }

        if (isset($params['search_term'])) {
            if (isset($params['search_term']['contain'])) {
                $query = $query->where(function ($query) use ($params) {
                    foreach ($params['search_term']['contain'] as $searchTerm) {
                        $query = $query->orWhere(function ($query) use ($searchTerm) {
                            $query->where('sku', 'LIKE', '%' . $searchTerm . '%')
                                ->orWhere('title', 'LIKE', '%' . $searchTerm . '%')
                                ->orWhere('description', 'LIKE', '%' . $searchTerm . '%')
                                ->orWhere('alternative_part_number', 'LIKE', '%' . $searchTerm . '%');
                        });
                    }
                });
            }

            if (isset($params['search_term']['dncontain'])) {
                $query = $query->where(function ($query) use ($params) {
                    foreach ($params['search_term']['dncontain'] as $searchTerm) {
                        $query = $query->where(function ($query) use ($searchTerm) {
                            $query->where('sku', 'NOT LIKE', '%' . $searchTerm . '%')
                                ->where('title', 'NOT LIKE', '%' . $searchTerm . '%')
                                ->where('description', 'NOT LIKE', '%' . $searchTerm . '%')
                                ->where('alternative_part_number', 'NOT LIKE', '%' . $searchTerm . '%');
                        });
                    }
                });
            }

            if (!isset($params['search_term']['dncontain']) && !isset($params['search_term']['contain'])) {
                $query = $query->where(function ($q) use ($params) {
                    $q->where('sku', 'LIKE', '%' . $params['search_term'] . '%')
                        ->orWhere('title', 'LIKE', '%' . $params['search_term'] . '%')
                        ->orWhere('description', 'LIKE', '%' . $params['search_term'] . '%')
                        ->orWhere('alternative_part_number', 'LIKE', '%' . $params['search_term'] . '%');
                });
            }
        }

        if (isset($params['sort'])) {
            $query = $this->addSortQuery($query, $params['sort']);
        }

        return $query->paginate($params['per_page'])->appends($params);
    }

    public function getAllByDealerId($dealerId)
    {
        return Part::where('dealer_id', $dealerId)->get();
    }

    /**
     * Get all rows by dealerId.
     * note: used by csv export
     * @param $dealerId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function queryAllByDealerId($dealerId)
    {
        return Part::with(['vendor', 'brand', 'images', 'bins'])->where('dealer_id', $dealerId);
    }

    public function update($params) {
        /** @var Part $part */
        $part = $this->get($params);

        DB::transaction(function() use (&$part, $params) {

            if (isset($params['is_vehicle_specific']) && $params['is_vehicle_specific']) {
                VehicleSpecific::updateOrCreate([
                    'make' => $params['vehicle_make'],
                    'model' => $params['vehicle_model'],
                    'year_from' => $params['vehicle_year_from'],
                    'year_to' => $params['vehicle_year_to'],
                    'part_id' => $part->id
                ]);
            }

            $part->fill($params);

            if (!isset($params['vendor_id'])) {
                $part->vendor_id = null;
            }

            if ($part->save()) {
                if (isset($params['images'])) {
                    $part->images()->delete();
                    foreach($params['images'] as $image) {
                        try {
                            $this->storeImage($part->id, $image);
                        } catch (\ImageNotDownloadedException $ex) {

                        }
                    }
                }

                if (isset($params['bins'])) {
                    $part->bins()->delete();
                    $part->load('bins');

                    foreach ($params['bins'] as $bin) {
                        $binQty = $this->createBinQuantity([
                            'part_id' => $part->id,
                            'bin_id' => $bin['bin_id'],
                            'qty' => $bin['quantity']
                        ]);
                        $part->bins->add($binQty);
                    }
                }
            }

        });

        return $part;
    }

    private function storeImage($partId, $image) {
        $explodedImage = explode('.', $image['url']);
        $imageExtension = $explodedImage[count($explodedImage) - 1];
        $fileName = md5($partId)."/".uniqid().".{$imageExtension}";

        try {
            $imageData = file_get_contents($image['url'], false, stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]));
        } catch (\Exception $ex) {
            return;
            throw new ImageNotDownloadedException('Image not accessible: '.$image['url']);
        }

        Storage::disk('s3')->put($fileName, $imageData, 'public');
        $s3ImageUrl = Storage::disk('s3')->url($fileName);

        PartImage::create([
            'part_id' => $partId,
            'image_url' => $s3ImageUrl,
            'position' => $image['position']
        ]);
    }

    protected function getSortOrders() {
        return $this->sortOrders;
    }

    /** @return BinQuantity */
    public function createBinQuantity($params)
    {
        return BinQuantity::create($params);
    }

    public function createPart($params)
    {
        return Part::create($params);
    }

    /**
     * @param $query
     * @param $dealerId
     * @param  array  $options Options: allowAll
     * @param  LengthAwarePaginator|null  $paginator Put a avr here, it will be given a paginator if `page` param is set
     * @return mixed
     * @throws \Exception
     */
    public function search($query, $dealerId, $options = [], &$paginator = null)
    {
        $search = Part::boolSearch();

        if ($query['query'] ?? null) { // if a query is specified
            $search->must('multi_match', [
                'query' => $query['query'],
                'fuzziness' => 'AUTO',
                'fields' => ['title^1.3', 'part_id^3', 'sku^3', 'brand', 'manufacturer', 'type', 'category', 'alternative_part_number^2', 'description^0.5']
            ]);

        } else if ($options['allowAll'] ?? false) { // if no query supplied but is allowed
            $search->must('match_all', []);

        } else {
            throw new \Exception('Query is required');
        }

        // vendor id
        if ($query['vendor_id'] ?? null) {
            $search->filter('term', ['vendor_id' => $query['vendor_id']]);
        }

        // if part has dealer cost
        if ($query['with_cost'] ?? false) {
            if ($query['with_cost'] == 1) {
                $search->filter('range', ['dealer_cost' => ['gt' => 0]]);
            } else if ($query['with_cost'] == 2) {
                $search->filter('term', ['dealer_cost' => 0]);
            }
        }

        // if part is in stock
        if ($query['in_stock'] ?? false) {
            if ($query['in_stock'] == 1) {
                $search->filter('range', ['bins_total_qty' => ['gt' => 0]]);
            } else if ($query['in_stock'] == 2) {
                $search->filter('range', ['bins_total_qty' => ['lte' => 0]]);
            }
        }

        // filter by dealer
        $search->filter('term', ['dealer_id' => $dealerId]);

        // sort order
        if ($query['sort'] ?? null) {
            $sortDir = substr($query['sort'], 0, 1) === '-'? 'asc': 'desc';
            $field = str_replace('-', '', $query['sort']);
            if (array_key_exists($field, $this->indexKeywordFields)) {
                $field = $this->indexKeywordFields[$field];
            }

            $search->sort($field, $sortDir);
        }

        // load relations
        $search->load(['brand', 'manufacturer', 'type', 'category', 'images', 'bins']);

        // if a paginator is requested
        if ($options['page'] ?? null) {
            $page = $options['page'];
            $perPage = $options['per_page'] ?? 10;

            $search->from(($page - 1) * $perPage);
            $search->size($perPage);

            $searchResult = $search->execute();

            $paginator = new LengthAwarePaginator(
                $searchResult->models(),
                $searchResult->total(),
                $perPage,
                $page,
                [
                    'path' => Paginator::resolveCurrentPath(),
                    'pageName' => 'page',
                ]
            );

            return $searchResult->models();
        }

        // if no paginator, set a default return size
        $size = $options['size'] ?? 50;
        $search->size($size);

        return $search->execute()->models();
    }

}
