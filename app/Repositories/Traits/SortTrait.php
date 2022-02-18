<?php

namespace App\Repositories\Traits;

use App\Exceptions\NotImplementedException;

trait SortTrait {
    
    private $sorts = [
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
        ]
    ];
    
    public function getSortFields()
    {
        $data = [];
        foreach($this->sortOrders as $sort => $val) {
            $data[] = [
                'param' => $sort,
                'front_name' => $this->getSortOrderNames()[$sort]['name']
            ];
        }        
        return $data;
    }
        
    protected function addSortQuery($query, $sort) {
        if (!isset($this->getSortOrders()[$sort])) {
            return $query;
        }

        // Get Sort Order(s)
        $orders = $this->getSortOrders()[$sort];
        if(isset($orders['field'])) {
            return $query->orderByRaw($orders['field'] . ' ' . $orders['direction']);
        }

        // Handle Multi-Column Orders
        foreach($orders as $order) {
            $query = $query->orderByRaw($order['field'] . ' ' . $order['direction']);
        }
        return $query;
    }
    
    protected function getSortOrderNames() {
        throw new NotImplementedException;
    }
    
    protected function getSortOrders() {
        return $this->sorts;
    }
}
