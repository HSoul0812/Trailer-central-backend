<?php

namespace App\Repositories\Parts;

use App\Events\Parts\PartQtyUpdated;
use App\Repositories\Repository;
use App\Models\Parts\Part;
use App\Models\Parts\PartImage;
use Illuminate\Database\Query\Builder;
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
                $query = $query->where(function ($query) use ($params) {
                    foreach ($params['subcategory']['eq'] as $subcategory) {
                        $query->orWhere('subcategory', 'LIKE', '%' . $subcategory . '%');
                    }
                });
            }

            if (isset($params['subcategory']['neq'])) {
                $query = $query->where(function ($query) use ($params) {
                    foreach ($params['subcategory']['neq'] as $subcategory) {
                        $query->where('subcategory', 'NOT LIKE', '%' . $subcategory . '%');
                    }
                });
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
        // $part = Part::findOrFail($params['id']);
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
                    foreach ($params['bins'] as $bin) {
                        $binQty = $this->createBinQuantity([
                            'part_id' => $part->id,
                            'bin_id' => $bin['bin_id'],
                            'qty' => $bin['quantity']
                        ]);
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

}
