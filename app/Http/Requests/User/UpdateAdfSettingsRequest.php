<?php

namespace App\Http\Requests\User;

use App\Http\Requests\Request;
use App\Models\CRM\Leads\Export\LeadEmail;
use Illuminate\Database\Eloquent\Collection;
use App\DTO\CRM\Leads\Export\LeadEmail as LeadEmailDTO;

class UpdateAdfSettingsRequest extends Request
{
    protected $rules = [
        'dealer_id' => 'integer|min:1|required|exists:dealer,dealer_id',
        'settings' => 'required|array',
        'settings.*.email' => 'string',
        'settings.*.cc_email' => 'string',
        'settings.*.dealer_location_id' => 'integer',
    ];
    
    public function __construct(array $query = array(), array $request = array(), array $attributes = array(), array $cookies = array(), array $files = array(), array $server = array(), $content = null) {
        parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);
        $this->rules['settings.*.export_format'] = 'in:'.implode(',', LeadEmail::EXPORT_FORMAT_TYPES);
    }
        
    public function getLeadEmails()
    {
        $data = $this->all();
        $leadEmailDtoCollection = new Collection();

        foreach($data['settings'] as $setting) 
        {
            $leadEmailDto = new LeadEmailDTO((int)$data['dealer_id'],$setting['email'],(int)$setting['export_format'],$setting['cc_email'],(int)$setting['dealer_location_id']);
            $leadEmailDtoCollection->add($leadEmailDto);
        }
        
        return $leadEmailDtoCollection;
    }
    
    
}
