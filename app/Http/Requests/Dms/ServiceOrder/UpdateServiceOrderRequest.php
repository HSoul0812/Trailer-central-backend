<?php

namespace App\Http\Requests\Dms\ServiceOrder;

use App\Http\Requests\Request;
use App\Models\CRM\Dms\ServiceOrder;

class UpdateServiceOrderRequest extends Request {
    
    protected $rules = [
        'id' => 'integer|required|exists:dms_repair_order,id',
        'dealer_id' => 'integer|required|exists:dealer,dealer_id'
    ];
    
    public function __construct(array $query = array(), array $request = array(), array $attributes = array(), array $cookies = array(), array $files = array(), array $server = array(), $content = null) {
        parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);
        $this->rules['status'] = 'string|required|in:'.implode(',', array_keys(ServiceOrder::SERVICE_ORDER_STATUS));
    }
    
    protected function getObject()
    {
        return new ServiceOrder;
    }
    
    protected function validateObjectBelongsToUser(): bool
    {
        return true;
    }
    
    protected function getObjectIdValue()
    {
        return $this->input('id');
    }
}
