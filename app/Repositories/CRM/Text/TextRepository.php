<?php

namespace App\Repositories\CRM\Text;

use Illuminate\Support\Facades\DB;
use App\Repositories\CRM\Text\TextRepositoryInterface;
use App\Exceptions\NotImplementedException;
use App\Models\User\DealerLocation;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Interactions\TextLog;
use App\Models\CRM\Text\Stop;
use App\Services\CRM\Text\TwilioService;

class TextRepository implements TextRepositoryInterface {

    private $twilio;

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

    /**
     * TextRepository constructor.
     * @param TwilioService $service
     */
    public function __construct(TwilioService $service)
    {
        $this->twilio = $service;
    }
    
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
        $query = Template::where('id', '>', 0);
        
        if (!isset($params['per_page'])) {
            $params['per_page'] = 100;
        }

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

    /**
     * Send Text
     * 
     * @param type $params
     * @return type
     */
    public function send($params) {
        // Find Lead ID
        $lead = Lead::findOrFail($params['lead_id']);
        $dealerId = $lead->dealer_id;
        $locationId = $lead->dealer_location_id;
        if(empty($locationId) && !empty($lead->inventory->dealer_location_id)) {
            $locationId = $lead->inventory->dealer_location_id;
        }

        // Get User
        $user = $lead->crmUser;
        $fullName = '';
        if(!empty($user)) {
            $fullName = $user->first_name . ' ' . $user->last_name;
        }

        // Get From/To Numbers
        $phone = $params['phone'];
        $to_number = '+' . ((strlen($phone) === 11) ? $phone : '1' . $phone);
        $from_number = DealerLocation::findDealerNumber($dealerId, $locationId);

        // No From Number?!
        if(empty($from_number)) {
            return [
                'error' => 'No SMS Number found for current dealer!'
            ];
        }

        // Send Text to Twilio
        $text = $params['log_message'];
        $result = $this->twilio->send($from_number, $to_number, $text, $fullName);

        // Return Error?
        if(is_array($result) && isset($result['error'])) {
            return $result;
        }

        // Save Lead Status
        $lead->leadStatus()->first()->updateStatus(Lead::STATUS_MEDIUM);
        $lead->leadStatus()->first()->updateNextContactDate();

        // Log SMS
        return TextLog::create([
            'lead_id'     => $params['lead_id'],
            'from_number' => $from_number,
            'to_number'   => $to_number,
            'log_message' => $text
        ]);
    }
}
