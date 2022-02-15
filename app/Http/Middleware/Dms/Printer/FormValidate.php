<?php

namespace App\Http\Middleware\Dms\Printer;

use App\Http\Middleware\ValidRoute;
use App\Models\CRM\Dms\Printer\Form;

class FormValidate extends ValidRoute {

    const ID_PARAM = 'id';
    protected $params = [
        self::ID_PARAM => [
            'optional' => true,
            'message' => 'Printer Form does not exist.'
        ]
    ];

    protected $appendParams = [
        self::ID_PARAM => self::ID_PARAM
    ];

    protected $validator = [];
    
    public function __construct() {
        $this->validator[self::ID_PARAM] = function ($data) {
            $form = Form::find($data);
            return !empty($form->id);
        };
    }
}
