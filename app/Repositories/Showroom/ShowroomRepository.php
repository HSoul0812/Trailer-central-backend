<?php

namespace App\Repositories\Showroom;

use Illuminate\Database\Eloquent\Builder;
use App\Exceptions\NotImplementedException;
use Illuminate\Support\Facades\DB;
use App\Models\Showroom\Showroom;
use App\Models\Showroom\ShowroomImage;
use App\Models\Showroom\ShowroomFile;
use App\Exceptions\ImageNotDownloadedException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;

class ShowroomRepository implements ShowroomRepositoryInterface {

    private $sortOrders = [
        'year' => [
            'field' => 'year',
            'direction' => 'DESC'
        ],
        '-year' => [
            'field' => 'year',
            'direction' => 'ASC'
        ],
    ];

    public function create($params) {
        DB::beginTransaction();

        try {
            $showroom = Showroom::create($params);

            if (isset($params['images'])) {
                foreach ($params['images'] as $image) {
                    $isFloorplan = false;
                    if (isset($params['floorplan']) && $image == $params['floorplan']) {
                        $isFloorplan = true;
                    }

                    $this->storeImage($showroom->id, $image, $isFloorplan);
                }
            }

            if (isset($params['files'])) {
                foreach ($params['files'] as $file) {
                    $this->storeFile($showroom->id, $file);
                }
            }

             DB::commit();
        } catch (\ImageNotDownloadedException $ex) {
            DB::rollBack();
            throw new ImageNotDownloadedException($ex->getMessage());
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new \Exception($ex->getMessage());
        }

        return $showroom;
    }

    public function delete($params) {
        throw new NotImplementedException;
    }

    public function get($params)
    {
        return Showroom::findOrFail($params['id']);
    }

    public function getAll($params)
    {
        /** @var Builder $query */
        $query = Showroom::where('id', '>', 0);

        if (!empty($params['select']) && is_array($params['select'])) {
            $query->select($params['select']);
        }

        if (!empty($params['with']) && is_array($params['with'])) {
            foreach ($params['with'] as $with) {
                $query = $query->with($with);
            }
        }

        if (isset($params['search_term'])) {

            $query = $query->where(function ($q) use ($params) {
                $q->where('model', 'LIKE', '%' . $params['search_term'] . '%')
                    ->orWhere('manufacturer', 'LIKE', '%' . $params['search_term'] . '%')
                    ->orWhere('series', 'LIKE', '%' . $params['search_term'] . '%')
                ;
            });
        }

        if (isset($params['manufacturer'])) {
            $query = $query->where('manufacturer', '=', $params['manufacturer']);
        }

        if (isset($params['model'])) {
            $query = $query->where('model', '=', $params['model']);
        }

        if (isset($params['year'])) {
            $query = $query->where('year', '=', $params['year']);
        }

        if (!isset($params['per_page'])) {
            $params['per_page'] = 15;
        }

        if (isset($params['sort'])) {
            $query = $this->addSortQuery($query, $params['sort']);
        }

        return $query->paginate($params['per_page'])->appends($params);
    }

    public function update($params) {
        throw new NotImplementedException;
    }

    /**
     * @inheritDoc
     */
    public function distinctByManufacturers(): Collection
    {
        return Showroom::select('manufacturer')
            ->distinct()
            ->where('manufacturer', '!=', '')
            ->whereNotNull('manufacturer')
            ->orderBy('manufacturer')
            ->get()
            ->pluck('manufacturer');
    }

    private function storeImage($showroomId, $image, $isFloorplan) {
        $explodedImage = explode('.', $image);
        $imageExtension = $explodedImage[count($explodedImage) - 1];
        $fileName = 'showroom-files/'.md5($showroomId)."/".uniqid().".{$imageExtension}";

        try {
            $imageData = file_get_contents($image, false, stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]));
        } catch (\Exception $ex) {
            throw new ImageNotDownloadedException('Image not accessible: '.$image);
        }

        Storage::disk('s3')->put($fileName, $imageData);

        ShowroomImage::create([
            'showroom_id' => $showroomId,
            'src' => $fileName,
            'is_floorplan' => $isFloorplan ? 1 : 0
        ]);
    }

     private function storeFile($showroomId, $file) {
        $explodedFile = explode('.', $file);
        $fileExtension = $explodedFile[count($explodedFile) - 1];

        $fileName = 'showroom-files/'.md5($showroomId)."/".uniqid().".{$fileExtension}";

        try {
            $fileData = file_get_contents($file, false, stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]));
        } catch (\Exception $ex) {
            throw new ImageNotDownloadedException('Image not accessible: '.$file);
        }

        Storage::disk('s3')->put($fileName, $fileData);

        ShowroomFile::create([
            'showroom_id' => $showroomId,
            'src' => $fileName,
            'name' => $this->getFilename($file)
        ]);
    }

    private function getFilename($file) {
        $explodedFile = explode('/', $file);
        return $explodedFile[count($explodedFile) - 1];
    }

    private function addSortQuery($query, $sort) {
        if (!isset($this->sortOrders[$sort])) {
            return;
        }
        return $query->orderBy($this->sortOrders[$sort]['field'], $this->sortOrders[$sort]['direction']);
    }

}
