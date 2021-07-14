<?php

namespace App\Traits\Migrations\Integrations;

trait OutgoingIntegrationTrait 
{
    public function getDealerIdSettingsCode(string $label) : string
    {
        $labelLength = strlen($label);
        return 'a:1:{i:0;a:5:{s:4:"name";s:9:"dealer_id";s:5:"label";s:9:"Dealer ID";s:11:"description";s:'.$labelLength.':"'.$label.'";s:4:"type";s:4:"text";s:8:"required";b:1;}}';
    }
}
