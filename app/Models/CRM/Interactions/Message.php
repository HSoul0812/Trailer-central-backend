<?php

namespace App\Models\CRM\Interactions;

use ElasticScoutDriverPlus\CustomSearch;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Jenssegers\Model\Model as NoDbTableModel;
use Laravel\Scout\Searchable;

class Message extends NoDbTableModel
{
    use Searchable, CustomSearch;

    protected $primaryKey = 'interaction_messages';

    protected $fillable = [
        'id',
        'name',
    ];


    /**
     * @return bool
     */
    public function save($options = array()): bool
    {
        $this->searchable();

        return true;
    }

    /**
     * Create a new Eloquent Collection instance.
     *
     * @param  array  $models
     * @return Collection
     */
/*    public function newCollection(array $models = [])
    {
        return new Collection($models);
    }*/

    /**
     * Get the value of the model's primary key.
     *
     * @return mixed
     */
    public function getKey()
    {
        //print_r($this->getAttribute($this->getKeyName()));exit();

        return 123456;
    }

    public function getQueueableRelations()
    {
        return [];
    }

    /**
     * Get the primary key for the model.
     *
     * @return string
     */
    public function getKeyName()
    {
        return $this->primaryKey;
    }

    /**
     * @return bool|float|\Illuminate\Support\Collection|int|mixed|string|null
     */
    public function getConnectionName()
    {
        return '';
    }

    public function setConnection()
    {
        return true;
    }

    /**
     * @return string
     */
    public function searchableAs(): string
    {
        return 'interaction_messages';
    }

/*    public function newQueryForRestoration($ids)
    {
        return is_array($ids)
            ? $this->newQueryWithoutScopes()->whereIn($this->getQualifiedKeyName(), $ids)
            : $this->newQueryWithoutScopes()->whereKey($ids);
    }*/
}
