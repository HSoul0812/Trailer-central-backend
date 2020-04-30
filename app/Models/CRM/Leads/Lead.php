<?php

namespace App\Models\CRM\Leads;

use App\Models\CRM\Dms\Website;
use App\Models\CRM\Interactions\EmailHistory;
use App\Models\CRM\Interactions\Interaction;
use App\Models\CRM\Interactions\TextLog;
use App\Models\CRM\Leads\Product;
use App\Models\CRM\Product\Inventory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'website_lead';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'identifier';

    /**
     * Get the email history for the lead.
     */
    public function emailHistory()
    {
        return $this->hasMany(EmailHistory::class, 'lead_id', 'identifier');
    }

    /**
     * Get the email history for the lead.
     */
    public function interactions()
    {
        return $this->hasMany(Interaction::class, 'tc_lead_id', 'identifier');
    }

    /**
     * Get all products for the lead.
     */
    public function product()
    {
        return $this->hasManyThrough(Product::class, 'crm_lead_product', 'lead_id', 'identifier');
    }

    /**
     * Get all inventories for the lead.
     */
    public function inventory()
    {
        return $this->hasManyThrough(Inventory::class, 'crm_inventory_lead', 'website_lead_id', 'identifier');
    }

    /**
     * Get lead types.
     */
    public function leadTypes()
    {
        return $this->hasMany(LeadType::class, 'lead_id', 'identifier');
    }

    /**
     * Get lead types.
     */
    public function textLogs()
    {
        return $this->hasMany(TextLog::class, 'lead_id', 'identifier');
    }

    /**
     * Return All Product ID's for Current Lead
     *
     * @return array
     */
    public function getId() {
        return $this->processProperty(Utility::expand($this->identifier));
    }

    public function getProductIds() {
        return $this->product()->pluck('product_id')->toArray();
    }

    public function getInventoryIds() {
        return $this->inventory()->pluck('inventory_id')->toArray();
    }

    /**
     * Retrieves this lead status from the DB
     *
     * @return string
     */
    public function status() {
        return $this->hasOne(LeadStatus::class, 'tc_lead_identifier', 'identifier');
    }

    /**
     * Retrieves lead website
     *
     * @return string
     */
    public function website() {
        return $this->belongsTo(Website::class, 'id', 'website_id');
    }

    public function getStatus($id) {
        if(!empty($id)) {
            $status = $this->findOrFail($id)->status()->pluck('status')->toArray();
        } else {
            $status = $this->status()->pluck('status')->toArray();
        }
        return $status['status'];
    }

    /**
     * Retrieves this lead source from the DB
     *
     * @return string
     */
    public function getSource() {
        $source = $this->status()->pluck('source')->toArray();
        return $source['status'];
    }

    public function getStatusId() {
        return null;
    }

    /**
     * Get the user's full name.
     *
     * @return string
     */
    public function getFullNameAttribute() {
        return "{$this->first_name} {$this->last_name}";
    }

    public static function findLeadContact($id) {
        $result = Lead::findOrFail($id)->pluck('first_name', 'last_name', 'email_address')->toArray();
        return array('name' => $result['first_name'] .' '. $result['last_name'], 'email' => $result['email_address']);
    }

    /**
     * Get the user's full name.
     *
     * @return string
     */
    public function getFullAddressAttribute() {
        if(empty($this->address) || empty($this->city) || empty($this->state)) {
            return null;
        }
        return "{$this->address}, {$this->city}, {$this->state}, {$this->zip}";
    }

    /**
     * @return phone number in format (XXX) NNN-NNNN
     */
    public function getPhoneNumberAttribute() {
        if(  preg_match( '/^(\d{3})(\d{3})(\d{4})$/', $this->phone_number,  $matches ) ) {
            return '(' . $matches[1] . ')' . ' ' .$matches[2] . '-' . $matches[3];
        } else {
            return '(---) -------';
        }
    }

    /**
     * Match Upload Leads With API
     */
    public static function matchLeads($dealerId, $indexes) {
        // Get API URL
        $apiUrl = API_URL . 'dealer/' .
            Utility::shorten($dealerId) . '/website/leads';

        // Implement Options
        $options = array(
            CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => http_build_query($indexes),
            CURLOPT_POST => true
        );

        // Set Options
        $ch = curl_init($apiUrl);
        curl_setopt_array($ch, $options);

        // Get Results
        $content = curl_exec($ch);
        curl_close($ch);

        // Return CSV
        $result = json_decode($content);
        return empty($result) ? array() : $result->response;
    }

    /**
     * Process the property value to comply with what the interface methods expect
     *
     * @param mixed $property
     * @return mixed
     */
    private function processProperty($property) {
        return empty($property) ? null : $property;
    }

    /**
     * Basically groups Leads by last name and returns the result
     *
     * @param array $leads Array of Lead
     * @return array Associative array of leads
     */
    public static function buildSimilarLeads($dealerId, $leads) {
        // Get Indexes Array
        $indexArray = [];
        foreach($leads as $lead) {
            // Detect Email
            if(!empty($lead->email_address)) {
                // Add to Array
                $indexArray[] = array(
                    "type" => "email",
                    "identifier" => $lead->email_address
                );
            }

            // Detect Phone
            if(!empty($lead->phone_number)) {
                // Add to Array
                $indexArray[] = array(
                    "type" => "phone",
                    "identifier" => $lead->phone_number
                );
            }
        }

        // Return Lead Matches
        $response = Lead::matchLeads($dealerId, $indexArray);


        // Loop Matches
        foreach($leads as $index => $data) {
            $matches = array();
            if (!empty($response) && !empty($response->matches)) {
                foreach($response->matches as $match) {
                    // Toggle Preferred Contact
                    if(empty($match->identifier) || $match->identifier === $data->identifier) {
                        continue;
                    }
                    if((!empty($match->email_address) && strtolower($data->email_address) === strtolower($match->email_address)) ||
                        (!empty($match->phone_number) && $data->phone_number === $match->phone_number)) {
                        $matches[] = $match;
                    }
                }
            }

            // Applie Matches Array
            if(count($matches) > 0) {
                $leads[$index]->setSimilarLeads($matches);
            }
        }

        // Return Final Leads Array
        return $leads;
    }

    public function loadFromArray($arr) {
        foreach($arr as $key => $value) {
            if($key === 'status') {
                $value = Lead::getStatus((int)$value);
            } elseif($key === 'lead_type') {
                if(is_array($value)) {
                    $value = reset($value);
                }
            }
            $this->$key = $value;
        }
    }

    /**
     *
     * @param array $leads integer array of unshortened lead ids
     */
    public function mergeLeads($merges, $dealerId, $productLeadTable, $interactionsTable) {
        // Initialize Current Lead Data
        $earliestDateSubmitted = $this->getDateSubmitted();
        $latestInteractionDate = $this->getNextContactDate();

        // Loop Leads to Merge
        $leadArray = [];
        $notes = "";
        foreach($merges as $lead) {
            $leadArray[] = $lead;

            // Add to Notes
            if($lead->getNotes() !== '') {
                if(!empty($notes)) {
                    $notes .= "\n\n";
                }
                $notes .= $lead->getNotes();
            }

            // Find Earliest Date Submitted
            $dateSubmitted = strtotime($lead->getDateSubmitted());
            if(empty($earliestDateSubmitted) || $dateSubmitted < strtotime($earliestDateSubmitted)) {
                $earliestDateSubmitted = $lead->getDateSubmitted();
            }

            // Find Latest Contact Date
            $nextContactDate = strtotime($lead->getNextContactDate());
            if(empty($latestInteractionDate) || $nextContactDate < strtotime($latestInteractionDate)) {
                $latestInteractionDate = $lead->getNextContactDate();
            }
        }

        // Update Notes
        if(!empty($notes)) {
            // Save Notes
            if(!empty($this->note)) {
                $notes = "\n\n" . $notes;
            }
            $this->note .= $notes;
            $this->saveNote();
        }

        // Assign Earliest Date Submitted
        if(!empty($earliestDateSubmitted)) {
            $this->saveDateSubmitted($earliestDateSubmitted);
        }

        // Assign Next Date Submitted
        if(!empty($latestInteractionDate)) {
            $this->saveNextContactDate($latestInteractionDate);
        }

        // Loop Through Leads
        $mergedLeads = 0;
        $productIds = $this->getProductIds();
        $thisLeadId = $this->getIdentifier();
        foreach($leadArray as $lead) {
            // Set Old Lead ID
            $oldLeadId = $lead->getIdentifier();

            // Assign Interactions to Correct Lead
            $interactionSql = "
                UPDATE crm_interaction
                SET tc_lead_id = :newLeadId
                WHERE tc_lead_id = :oldLeadId
            ";
            $interactionStmt = $this->db->createStatement($interactionSql);
            $interactionStmt->execute(array(
                'newLeadId' => $thisLeadId,
                'oldLeadId' => $oldLeadId
            ));

            // Assign Interactions to Correct Lead
            $emailSql = "
                UPDATE crm_email_history
                SET lead_id = :newLeadId
                WHERE lead_id = :oldLeadId
            ";
            $emailStmt = $this->db->createStatement($emailSql);
            $emailStmt->execute(array(
                'newLeadId' => $thisLeadId,
                'oldLeadId' => $oldLeadId
            ));

            // Assign Website Lead Tracking to Correct Lead
            $trackingSql = "
                UPDATE website_tracking
                SET lead_id = :newLeadId
                WHERE lead_id = :oldLeadId
            ";
            $trackingStmt = $this->db->createStatement($trackingSql);
            $trackingStmt->execute(array(
                'newLeadId' => $thisLeadId,
                'oldLeadId' => $oldLeadId
            ));

            // Add Name to Comments
            $leadName = "";
            if(!empty($lead->first_name)) {
                $leadName .= $lead->first_name;
            }
            if(!empty($leadName) && !empty($lead->last_name)) {
                $leadName .= " ";
            }
            if(!empty($lead->last_name)) {
                $leadName .= $lead->last_name;
            }
            if(!empty($leadName)) {
                $leadName .= "<br /><br />";
            }

            // Get Email
            $leadEmail = "";
            if(!empty($lead->email_address)) {
                $leadEmail = $lead->email_address;
            }
            if(!empty($leadEmail)) {
                $leadEmail = "Email: " . $leadEmail . "<br /><br />";
            }

            // Get Phone
            $leadPhone = "";
            if(!empty($lead->phone_number)) {
                $leadPhone = $lead->phone_number;
            }
            if(!empty($leadPhone)) {
                $leadPhone = "Phone: " . $leadPhone . "<br /><br />";
            }

            // Move Comments to New Interaction
            $lead->comments = $leadName . $leadEmail . $leadPhone . $lead->comments;
            if(empty($lead->comments)) {
                $lead->comments = "Not Provided";
            }
            $inquiry = $interactionsTable->buildInteraction($dealerId,
                $this->getIdentifier(),
                $lead->getInventoryId(),
                'INQUIRY',
                'Original Inquiry: ' . $lead->comments,
                $lead->getDateSubmitted());
            $interactionsTable->saveInteraction($inquiry);

            // Match Product Lead
            if(!empty($lead->getInventoryId()) && !in_array($lead->getInventoryId(), $productIds)) {
                $productLead = new ProductLead();
                $productLead->inventory_id = $lead->getInventoryId();
                $productLead->website_lead_id = $this->getIdentifier();
                $productLeadTable->save($productLead);
                $productIds[] = $lead->getInventoryId();
            }

            // Get Products
            foreach($lead->getProductIds() as $productId) {
                if(!in_array($productId, $productIds)) {
                    $productLead = new ProductLead();
                    $productLead->inventory_id = $productId;
                    $productLead->website_lead_id = $this->getIdentifier();
                    $productLeadTable->save($productLead);
                    $productIds[] = $productId;
                }
            }

            // Delete Lead
            $lead->delete();
            $mergedLeads++;
        }

        // Return Success
        return $mergedLeads;
    }

    /**
     * Get Purchases for Lead
     *
     * @param int $leadId
     * @return array of result data
     * @throws \Exception
     */
    public function getInvoices() {
        // Get Purchases
        if(empty($this->invoices)) {
            // Get Invoices From DB
            $sql = "
                SELECT us.*, us.total_price - COALESCE(usp.paid_amount, 0) AS remaining_amount, COALESCE(usp.paid_amount, 0) AS paid_amount
                FROM dms_unit_sale AS us
                    LEFT JOIN qb_invoices AS i ON i.unit_sale_id = us.id
                    LEFT JOIN (
                        SELECT sum(amount) AS paid_amount, invoice_id
                        FROM qb_payment
                        GROUP BY invoice_id
                    ) AS usp ON usp.invoice_id=i.id
                WHERE
                    us.lead_id={$this->getIdentifier()}
            ";
            $resultSet = $this->db->query($sql, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);

            // Loop Purchases
            $invoices = array();
            foreach($resultSet as $result) {
                $invoices[] = $result;
            }
            $this->invoices = $invoices;
        }

        // Return Results
        return $this->invoices;
    }

    /**
     * Get Total Purchases for Lead
     *
     * @param int $leadId
     * @return array of result data
     * @throws \Exception
     */
    public function getLifetimeSales() {
        // Get Sales
        if(empty($this->lifetime_sales)) {
            // Get Invoices From DB
            $sql = "
                SELECT sum(usp.paid_amount) AS paid_amount
                FROM dms_unit_sale AS us
                    LEFT JOIN qb_invoices AS i ON i.unit_sale_id = us.id
                    LEFT JOIN (
                        SELECT sum(amount) AS paid_amount, invoice_id
                        FROM qb_payment
                        GROUP BY invoice_id
                    ) AS usp ON usp.invoice_id=i.id
                WHERE
                    us.lead_id={$this->getIdentifier()}
            ";
            $resultSet = $this->db->query($sql, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);

            // Loop Purchases
            foreach($resultSet as $result) {
                $this->lifetime_sales = $result->paid_amount;
            }
        }

        // Return Sales
        if($this->lifetime_sales > 0) {
            return number_format(round($this->lifetime_sales, 2), 2);
        }
        return 0;
    }
}
