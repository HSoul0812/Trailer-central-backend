<?php

namespace App\Repositories\CRM\Text;

use Illuminate\Support\Facades\DB;
use App\Repositories\CRM\Text\TextRepositoryInterface;
use App\Repositories\User\DealerLocationRepositoryInterface;
use App\Exceptions\CRM\Text\NoLeadSmsNumberAvailableException;
use App\Exceptions\CRM\Text\NoDealerSmsNumberAvailableException;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Interactions\TextLog;
use App\Models\CRM\Text\Stop;
use App\Services\CRM\Text\TextServiceInterface;
use Carbon\Carbon;

class TextRepository implements TextRepositoryInterface {

    /**
     * @var TextServiceInterface
     */
    private $service;

    /**
     * @var DealerLocationRepositoryInterface
     */
    private $dealerLocation;

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
     * 
     * @param TextServiceInterface $service
     */
    public function __construct(TextServiceInterface $service, DealerLocationRepositoryInterface $dealerLocation)
    {
        $this->service = $service;
        $this->dealerLocation = $dealerLocation;
    }
    
    public function create($params) {
        return TextLog::create($params);
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

    public function stop($params) {
        return Stop::create($params);
    }

    /**
     * Send Text
     * 
     * @param int $leadId
     * @param string $textMessage
     * @return type
     */
    public function send($leadId, $textMessage) {
        // Get Lead/User
        $lead = Lead::findOrFail($leadId);
        $fullName = $lead->newDealerUser()->first()->crmUser->full_name;

        // Get To Numbers
        $to_number = $lead->text_phone;
        if(empty($to_number)) {
            throw new NoLeadSmsNumberAvailableException();
        }

        // Get From Number
        $from_number = $this->dealerLocation->findDealerNumber($lead->dealer_id, $lead->preferred_location);
        if(empty($from_number)) {
            throw new NoDealerSmsNumberAvailableException();
        }

        // Send Text
        $this->service->send($from_number, $to_number, $textMessage, $fullName);

        // Save Lead Status
        $this->updateLeadStatus($lead);

        // Log SMS
        return $this->create([
            'lead_id'     => $leadId,
            'from_number' => $from_number,
            'to_number'   => $to_number,
            'log_message' => $textMessage
        ]);
    }


    /**
     * Update Status for Lead
     * 
     * @param Lead $lead
     * @return LeadStatus
     */
    private function updateLeadStatus($lead) {
        $leadStatus = $lead->leadStatus()->first();

        DB::transaction(function() use (&$leadStatus) {
            // Fill Text Details
            $leadStatus->fill([
                'status' => Lead::STATUS_MEDIUM,
                'next_contact_date' => Carbon::now()->addDay()->toDateTimeString()
            ])->save();
        });

        return $leadStatus;
    }

    /**
     * Add Sort Query
     * 
     * @param type $query
     * @param type $sort
     * @return type
     */
    private function addSortQuery($query, $sort) {
        if (!isset($this->sortOrders[$sort])) {
            return;
        }

        return $query->orderBy($this->sortOrders[$sort]['field'], $this->sortOrders[$sort]['direction']);
    }
}