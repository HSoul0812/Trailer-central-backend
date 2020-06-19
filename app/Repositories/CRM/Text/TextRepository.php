<?php

namespace App\Repositories\CRM\Text;

use App\Repositories\CRM\Text\TextRepositoryInterface;
use App\Exceptions\NotImplementedException;
use App\Models\CRM\Interactions\TextLog;
use App\Models\CRM\Text\Stop;

class TextRepository implements TextRepositoryInterface {

    private $sortOrders = [
        'date_sent' => [
            'field' => 'date_sent',
            'direction' => 'DESC'
        ],
        '-date_sent' => [
            'field' => 'date_sent',
            'direction' => 'ASC'
        ]
    ];
    
    public function create($params) {
        DB::beginTransaction();

        try {
            // Create Text
            $text = TextLog::create($params);

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new \Exception($ex->getMessage());
        }
        
        return $text;
    }

    public function delete($params) {
        $text = TextLog::findOrFail($params['id']);

        DB::transaction(function() use (&$text, $params) {
            $params['deleted'] = '1';

            $text->fill($params)->save();
        });

        return $text;
    }

    public function get($params) {
        return TextLog::findOrFail($params['id']);
    }

    public function getAll($params) {
        $query = TextLog::where('identifier', '>', 0);

        if (isset($params['lead_id'])) {
            $query = $query->where('lead_id', $params['lead_id']);
        }

        if (isset($params['id'])) {
            $query = $query->whereIn('id', $params['id']);
        }

        if (isset($params['sort'])) {
            $query = $this->addSortQuery($query, $params['sort']);
        }
        
        return $query->paginate($params['per_page'])->appends($params);
    }

    public function update($params) {
        $text = TextLog::findOrFail($params['id']);

        DB::transaction(function() use (&$text, $params) {
            // Fill Text Details
            $text->fill($params)->save();
        });

        return $text;
    }

    private function addSortQuery($query, $sort) {
        if (!isset($this->sortOrders[$sort])) {
            return;
        }

        return $query->orderBy($this->sortOrders[$sort]['field'], $this->sortOrders[$sort]['direction']);
    }

    public function stop($params) {
        DB::beginTransaction();

        try {
            // Create Stop
            $stop = Stop::create($params);

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new \Exception($ex->getMessage());
        }
        
        return $stop;
    }

}
