<?php

namespace App\Repositories\Website\Forms;

use App\Exceptions\NotImplementedException;
use App\Models\Website\Forms\FieldMap;
use App\Transformers\Website\Forms\FieldMapTransformer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class FieldMapRepository
 * @package App\Repositories\Website\Forms
 */
class FieldMapRepository implements FieldMapRepositoryInterface
{
    /**
     * Initialize Field Map Transformer
     */
    public function __construct(FieldMapTransformer $transformer) {
        $this->transformer = $transformer;
    }

    /**
     * @param $params
     * @throws NotImplementedException
     */
    public function create($params)
    {
        // Find FieldMap Entry
        $fieldMap = FieldMap::where('type', $params['type'])
                            ->where('form_field', $params['form_field'])
                            ->first();
        if(!empty($fieldMap->id)) {
            return $this->update($params);
        }

        // Get DB Table
        if(!isset($params['db_table'])) {
            $params['db_table'] = FieldMap::MAP_TABLES[$params['type']];
        }

        // Map Field Empty?
        if(empty($params['map_field'])) {
            $params['map_field'] = '';
        }

        // Create Post
        return FieldMap::create($params);
    }

    /**
     * @param $params
     * @throws NotImplementedException
     */
    public function update($params)
    {
        // ID Exists?
        if(isset($params['id'])) {
            // Get Field Map
            $fieldMap = FieldMap::findOrFail($params['id']);
        } else {
            // Find FieldMap Entry
            $fieldMap = FieldMap::where('type', $params['type'])->where('form_field', $params['form_field'])->first();
        }

        DB::beginTransaction();
        try {
            // Map Field Empty?
            if((array_key_exists('map_field', $params) && $params['map_field'] === null) ||
               (empty($fieldMap->map_field) && empty($params['map_field']))) {
                $params['map_field'] = '';
            }

            // Update Field Map
            $fieldMap->fill($params)->save();

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new \Exception($ex->getMessage());
        }

        // Return Field Map
        return $fieldMap;
    }

    /**
     * @param $params
     * @throws NotImplementedException
     */
    public function get($params)
    {
        // Type and Form Field Exists?
        if(isset($params['type']) && isset($params['form_field'])) {
            // Find FieldMap Entry
            return FieldMap::where('type', $params['type'])->where('form_field', $params['form_field'])->first();
        }

        // Return Field Map
        return FieldMap::findOrFail($params['id']);
    }

    /**
     * @param $params
     * @throws NotImplementedException
     */
    public function delete($params)
    {
        // Type and Form Field Exists?
        if(isset($params['type']) && isset($params['form_field'])) {
            // Find FieldMap Entry
            return FieldMap::where('type', $params['type'])
                           ->where('form_field', $params['form_field'])
                           ->delete();
        }

        // Return Field Map
        return FieldMap::where('id', $params['id'])->delete();
    }

    /**
     * @param $params
     * @return Collection
     */
    public function getAll($params): Collection
    {
        $query = FieldMap::select('*');

        if (isset($params['type'])) {
            $query->where('type', $params['type']);
        }

        return $query->get();
    }

    /**
     * Get Map of Field Data
     * 
     * @param $params
     * @return Collection
     */
    public function getMap($params)
    {
        // Get All Sorted by Types
        $types = [];
        foreach(FieldMap::MAP_TYPES as $type => $name) {
            // Type Exists?
            if(isset($params['type']) && $params['type'] !== $type) {
                continue;
            }

            // Get Fields By Type
            $fields = $this->getAll(['type' => $type]);
            $types[$type] = $fields->mapWithKeys(function($fieldMap) {
                return [$fieldMap->form_field => $this->transformer->transform($fieldMap)];
            });
        }

        // Return Sorted Types Array
        return $types;
    }

    /**
     * Get Field Map Types
     * 
     * @return Collection
     */
    public function getTypes()
    {
        // Get All Field Map Types
        return collect(array_keys(FieldMap::MAP_TYPES));
    }
}
