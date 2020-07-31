<?php

namespace App\Repositories\CRM\Leads;

use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use App\Repositories\CRM\Leads\InquiryRepositoryInterface;
use App\Repositories\CRM\Interactions\InteractionsRepositoryInterface;
use App\Exceptions\NotImplementedException;
use App\Services\CRM\Leads\InquiryEmailServiceInterface;
use App\Models\CRM\Leads\Lead;
use App\Repositories\Traits\SortTrait;

class InquiryRepository implements InquiryRepositoryInterface {

    use SortTrait;

    /**
     * @const int Number of Matches Required for Merge
     */
    const MERGE_MATCH_COUNT = 2;

    /**
     * @var InquiryEmailServiceInterface
     */
    private $inquiry;

    /**
     * @var LeadRepositoryInterface
     */
    private $leads;

    /**
     * @var InteractionsRepositoryInterface
     */
    private $interactions;

    /**
     * InquiryRepository constructor.
     * 
     * @param InquiryEmailRepositoryInterface
     * @param LeadsRepositoryInterface;
     * @param InteractionsRepositoryInterface
     */
    public function __construct(InquiryEmailServiceInterface $inquiry, LeadRepositoryInterface $leads, InteractionsRepositoryInterface $interactions)
    {
        $this->inquiry = $inquiry;
        $this->leads = $leads;
        $this->interactions = $interactions;
    }

    /**
     * Create New Inquiry
     * 
     * @param type $params
     * @return type
     */
    public function create($params) {
        // Create Lead
        $lead = $this->mergeOrCreate($params);

        // Valid Lead?!
        if(!empty($lead->identifier)) {
            $this->inquiry->send($lead->identifier, $params);
        }

        // Return Lead
        return $lead;
    }

    public function delete($params) {
        throw new NotImplementedException;
    }

    public function get($params) {
        throw new NotImplementedException;
    }

    public function getAll($params) {
        throw new NotImplementedException();
    }

    public function update($params) {
        throw new NotImplementedException;
    }

    /**
     * Merge Lead
     * 
     * @param Lead $lead
     * @param array $params
     */
    public function merge($lead, $params) {
        // Configure Notes From Provided Data
        $notes = '';
        if(!empty($params['first_name'])) {
            $notes .= $params['first_name'];
        }
        if(!empty($params['last_name'])) {
            if(!empty($notes)) {
                $notes .= ' ';
            }
            $notes .= $params['last_name'];
        }
        if(!empty($notes)) {
            $notes .= '<br /><br />';
        }

        // Add Phone/Email
        if(!empty($params['phone_number'])) {
            $notes .= 'Phone: ' . $params['phone_number'] . '<br /><br />';
        }
        if(!empty($params['email_address'])) {
            $notes .= 'Email: ' . $params['email_address'] . '<br /><br />';
        }
        if(!empty($params['comments'])) {
            $notes .= $params['comments'];
        }

        // Get Lead Data
        $this->interactions->create([
            'lead_id' => $lead->identifier,
            'interaction_type'   => 'INQUIRY',
            'interaction_notes'  => !empty($notes) ? 'Original Inquiry: ' . $notes : 'Not Provided'
        ]);

        // Return Lead
        return Lead::find($lead->identifier);
    }

    /**
     * Merge or Create Lead
     * 
     * @param array $params
     * @return Lead
     */
    public function mergeOrCreate($params) {
        // Find Matching Lead!
        $lead = $this->findMatch($params);

        // Merge Lead!
        if(!empty($lead->identifier)) {
            return $this->merge($lead, $params);
        }

        // Create!
        return $this->leads->create($params);
    }

    /**
     * Find Matching Lead
     * 
     * @return Lead
     */
    public function findMatch($params) {
        // Get Matches
        $leads = $this->findAllMatches($params);
        if(empty($leads)) {
            return null;
        }

        // Clean Phones
        $params['phone1'] = preg_replace('/[-+)( ]+/', '', $params['phone_number']);
        $params['phone2'] = '1' . $params['phone1'];
        if(strlen($params['phone1']) === 11) {
            $params['phone2'] = substr($params['phone1'], 1);
        }

        // Choose Matching Lead
        return $this->chooseMatch($leads, $params);
    }

    /**
     * Find Existing Lead That Matches Current Lead!
     * 
     * @param array $params
     * @return Collection of Lead 
     */
    public function findAllMatches($params) {
        // Dealer ID Exists?!
        if(!isset($params['dealer_id'])) {
            return null;
        }

        // Clean Phones
        $params['phone1'] = preg_replace('/[-+)( ]+/', '', $params['phone_number']);
        $params['phone2'] = '1' . $params['phone1'];
        if(strlen($params['phone1']) === 11) {
            $params['phone2'] = substr($params['phone1'], 1);
        }

        // Find Leads That Match Current!
        $lead = Lead::where('dealer_id', $params['dealer_id']);

        // Find Name
        return $lead->where(function(Builder $query) use($params) {
            return $query->where(function(Builder $query) use($params) {
                return $query->where('first_name', $params['first_name'])
                             ->where('last_name', $params['last_name']);
            })->orWhere(function(Builder $query) use($params) {
                return $query->whereRaw('REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(`phone_number`, \'+\', \'\'), \'-\', \'\'), \'(\', \'\'), \')\', \'\'), \' \', \'\') = ?', $params['phone1'])
                             ->where('phone_number', '<>', '');
            })->orWhere(function(Builder $query) use($params) {
                return $query->whereRaw('REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(`phone_number`, \'+\', \'\'), \'-\', \'\'), \'(\', \'\'), \')\', \'\'), \' \', \'\') = ?', $params['phone2'])
                             ->where('phone_number', '<>', '');
            })->orWhere(function(Builder $query) use($params) {
                return $query->where('email_address', $params['email_address'])
                             ->where('email_address', '<>', '');
            });
        })->get();
    }

    /**
     * Choose Matching Lead
     * 
     * @param Collection $matches
     * @param array $params
     * @return null | Lead
     */
    private function chooseMatch($matches, $params) {
        // Sort Leads Into Standard or With Status
        $leads = array();
        $status = array();
        foreach($matches as $lead) {
            if(!empty($lead->identifier)) {
                // Clean Up Phone
                $lead->phone_number = preg_replace('/[-+)( ]+/', '', $lead->phone_number);

                // Clean Up Email Address
                $lead->email_address = strtolower(trim($lead->email_address));

                // Clean Up Name
                $lead->first_name = strtolower(trim($lead->first_name));
                $lead->last_name = strtolower(trim($lead->last_name));

                // Add to Array
                $leads[] = $lead;
                if(!empty($lead->leadStatus)) {
                    $status[] = $lead;
                }
            }
        }

        // No Leads?
        if(empty($leads)) {
            return null;
        }

        // Find By Status!
        if(!empty($status) && count($status) > 0) {
            $chosen = $this->filterMatch($status, $params);
        }

        // Still Not Chosen? Find Any!
        if(empty($chosen)) {
            $chosen = $this->filterMatch($leads, $params);
        }

        // Return $result
        return $chosen;
    }

    /**
     * Filter Matching Lead
     * 
     * @param Collection $leads
     * @param array $params
     * @return null | Lead
     */
    private function filterMatch($leads, $params) {
        // Loop Status
        $chosen = null;
        $matches = array();
        foreach($leads as $single) {
            // Match Phone?
            $matched = 0;
            if($params['phone1'] === $single->phone_number || $params['phone2'] === $single->phone_number) {
                $matched++;
            }

            // Matched Email?
            if($params['email_address'] === $single->email_address) {
                $matched++;
            }

            // Matched Name?
            if($params['first_name'] === $single->first_name && $params['last_name'] === $single->last_name) {
                $matched++;
            }

            // Matched At Least Two?
            if($matched > self::MERGE_MATCH_COUNT) {
                $matches = array();
                $chosen = $single;
                break;
            } elseif($matched >= self::MERGE_MATCH_COUNT) {
                $matches[] = $single;
            }
        }

        // Get First Match
        if(empty($chosen) && count($matches) > 0) {
            $chosen = reset($matches);
        }

        // Return Array Mapping
        return !empty($chosen->identifier) ? $chosen : null;
    }
}