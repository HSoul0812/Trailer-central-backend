<?php

namespace App\Services\CRM\Text;

use App\Exceptions\CRM\Text\BlastException;
use App\Exceptions\CRM\Text\CustomerLandlineNumberException;
use App\Exceptions\CRM\Text\NoBlastSmsFromNumberException;
use App\Exceptions\CRM\Text\NoLeadsDeliverBlastException;
use App\Exceptions\CRM\Text\NotValidFromNumberException;
use App\Models\CRM\Interactions\TextLog;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\LeadStatus;
use App\Models\CRM\Text\Blast;
use App\Models\CRM\Text\BlastSent;
use App\Models\User\NewDealerUser;
use App\Services\CRM\Text\TwilioServiceInterface;
use App\Repositories\CRM\Leads\StatusRepositoryInterface;
use App\Repositories\CRM\Text\TextRepositoryInterface;
use App\Repositories\CRM\Text\BlastRepositoryInterface;
use App\Repositories\CRM\Text\TemplateRepositoryInterface;
use App\Repositories\User\DealerLocationRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Class BlastService
 *
 * @package App\Services\CRM\Text
 */
class BlastService implements BlastServiceInterface
{
    /**
     * @var TwilioServiceInterface
     */
    protected $textService;

    /**
     * @var App\Repositories\CRM\Leads\StatusRepository
     */
    protected $leadStatus;

    /**
     * @var App\Repositories\CRM\Text\TextRepository
     */
    protected $texts;

    /**
     * @var App\Repositories\CRM\Text\BlastRepository
     */
    protected $blasts;

    /**
     * @var App\Repositories\CRM\Text\TemplateRepository
     */
    protected $templates;

    /**
     * @var App\Repositories\User\DealerLocationRepository
     */
    protected $dealerLocation;

    /**
     * @var Log
     */
    protected $log;


    /**
     * BlastService constructor.
     */
    public function __construct(TwilioServiceInterface            $text,
                                StatusRepositoryInterface         $leadStatus,
                                TextRepositoryInterface           $textRepo,
                                BlastRepositoryInterface          $blastRepo,
                                TemplateRepositoryInterface       $templateRepo,
                                DealerLocationRepositoryInterface $dealerLocationRepo)
    {
        // Initialize Text Service
        $this->textService = $text;

        // Initialize Repositories
        $this->leadStatus = $leadStatus;
        $this->texts = $textRepo;
        $this->blasts = $blastRepo;
        $this->templates = $templateRepo;
        $this->dealerLocation = $dealerLocationRepo;

        // Initialize Logger
        $this->log = Log::channel('textcampaign');
    }

    /**
     * Send Blast Text
     *
     * @param NewDealerUser $dealer
     * @param Blast $blast
     * @return Collection<BlastSent>
     * @throws NoLeadsDeliverBlastException
     * @throws \Exception
     * @throws NoBlastSmsFromNumberException
     */
    public function send(NewDealerUser $dealer, Blast $blast): Collection {
        try {
            // Get From Number
            $from_number = $this->getFromNumber($dealer->id, $blast);

            if (!$this->textService->isValidPhoneNumber($from_number)) {
                $this->log->error('From SMS Number is Invalid #: ' . $dealer->id);
                throw new NotValidFromNumberException();
            }

            // Get Unsent Blast Leads
            if (count($blast->leads) < 1) {
                $this->log->error('No Leads found for Blast #' . $blast->id . ' for Dealer #: ' . $dealer->id);
                throw new NoLeadsDeliverBlastException();
            }

            // Loop Leads for Current Dealer
            $sent = new Collection();
            $this->log->debug('Found ' . $blast->leads->count() . ' Leads for Blast #' . $blast->id);

            foreach ($blast->leads as $lead) {
                // Not a Valid To Number?!
                if (empty($lead->text_phone)) {
                    continue;
                }

                // Send Lead
                $leadSent = $this->sendToLead($from_number, $dealer, $blast, $lead);
                if ($leadSent !== null) {
                    $sent->push($leadSent);
                }
            }

            // Mark Blast as Delivered
            $this->markDelivered($blast);

            $this->saveLog($blast, 'success', 'Successfully Delivered');

            // Return Blast Sent Entries
            return $sent;
        } catch (NoLeadsDeliverBlastException $e) {
            $this->saveLog($blast, 'warning', $e->getMessage());
            $this->markDelivered($blast);

            return new Collection();
        } catch (BlastException $e) {
            $this->saveLog($blast, 'error', $e->getMessage());
            $this->markDelivered($blast, true);

            throw $e;
        } catch (\Exception $e) {
            $this->saveLog($blast, 'error', 'An Unknown Error Has Occurred, Please Contact Support');
            $this->markDelivered($blast, true);

            throw $e;
        }
    }


    /**
     * Get From Number
     *
     * @param int $dealerId
     * @param Blast $blast
     * @return string
     */
    private function getFromNumber(int $dealerId, Blast $blast): string {
        // Get From Number
        $chosenNumber = $blast->from_sms_number;
        if(!empty($chosenNumber)) {
            return $chosenNumber;
        }

        // Get First Available Number From Dealer Location
        $fromNumber = $this->dealerLocation->findDealerSmsNumber($dealerId);
        if(!empty($fromNumber)) {
            return $fromNumber;
        }

        // Throw Exception
        $this->log->error('No Blast SMS From Number for Dealer #: ' . $dealerId);
        throw new NoBlastSmsFromNumberException;
    }

    /**
     * Send Text to Lead
     *
     * @param string $from_number sms from number
     * @param NewDealerUser $dealer
     * @param Blast $blast
     * @param Lead $lead
     * @return BlastSent
     */
    private function sendToLead(string $from_number, NewDealerUser $dealer,
                                Blast $blast, Lead $lead): BlastSent {
        // Get Text Message
        $textMessage = $this->templates->fillTemplate($blast->template->template, [
            'lead_name' => $lead->full_name,
            'title_of_unit_of_interest' => $lead->inventory_title,
            'dealer_name' => $dealer->user->name
        ]);

        try {
            // Send Text
            $this->textService->send($from_number, $lead->text_phone, $textMessage, $lead->full_name);
            $status = BlastSent::STATUS_SENT;

            // Update Lead Status
            if($this->updateLead($lead)) {
                $status = BlastSent::STATUS_LEAD;
            }

            // Save Text to DB
            $textLog = $this->saveText($from_number, $lead, $textMessage);
            if(!empty($textLog->id)) {
                $status = BlastSent::STATUS_LOGGED;
            }
        } catch (CustomerLandlineNumberException $ex) {
            $status = BlastSent::STATUS_LANDLINE;
            $this->log->error('Could not send text to number ' . $from_number . ': ' . $ex->getMessage());
        } catch (\Exception $ex) {
            $status = BlastSent::STATUS_INVALID;
            $this->log->error('Exception returned trying to send text: ' . $ex->getMessage() .
                                PHP_EOL . $ex->getTraceAsString());
        }

        // Return Sent Result
        return $this->markLeadSent($blast, $lead, $status, $textLog ?? null);
    }

    /**
     * Update Lead Status
     *
     * @param Lead $lead
     * @return LeadStatus
     */
    private function updateLead(Lead $lead): LeadStatus {
        // If there was no status, or it was uncontacted, set to medium, otherwise, don't change.
        if (empty($lead->leadStatus) || $lead->leadStatus->status === Lead::STATUS_UNCONTACTED) {
            $status = Lead::STATUS_MEDIUM;
        } else {
            $status = $lead->leadStatus->status;
        }

        return $this->leadStatus->createOrUpdate([
            'lead_id' => $lead->identifier,
            'status' => $status,
            'next_contact_date' => Carbon::now()->addDay()->toDateTimeString()
        ]);
    }

    /**
     * Save Text to DB
     *
     * @param string $from_number sms from number
     * @param Lead $lead
     * @param string $textMessage filled text message
     * @return TextLog
     */
    private function saveText(string $from_number, Lead $lead, string $textMessage): TextLog {
        // Log SMS
        return $this->texts->create([
            'lead_id'     => $lead->identifier,
            'from_number' => $from_number,
            'to_number'   => $lead->text_phone,
            'log_message' => $textMessage
        ]);
    }

    /**
     * Mark Lead as Sent
     *
     * @param Blast $blast
     * @param Lead $lead
     * @param string $status
     * @param null|TextLog $textLog
     * @return null|BlastSent
     */
    private function markLeadSent(Blast $blast, Lead $lead, string $status,
                                    ?TextLog $textLog = null): ?BlastSent {
        // Mark Blast as Sent to Lead
        try {
            return $this->blasts->sent([
                'text_blast_id' => $blast->id,
                'lead_id' => $lead->identifier,
                'text_id' => !empty($textLog->id) ? $textLog->id : 0,
                'status' => $status
            ]);
        } catch(\Exception $ex) {
            $this->log->error('Failed to mark lead as sent: ' . $ex->getMessage());
            return null;
        }
    }

    /**
     * Mark Blast as Delivered
     *
     * @param Blast $blast
     * @param bool $isError
     * @return Blast
     */
    private function markDelivered(Blast $blast, bool $isError = false): Blast
    {
        // Mark as Delivered
        return $this->blasts->update([
            'id' => $blast->id,
            'is_delivered' => 1,
            'is_error' => $isError,
        ]);
    }

    /**
     * @param Blast $blast
     * @param string $status
     * @param string $message
     * @return Blast
     */
    private function saveLog(Blast $blast, string $status, string $message): Blast
    {
        return $this->blasts->update([
            'id' => $blast->id,
            'log' => [
                'status' => $status,
                'message' => $message,
                'date' => (new \DateTime())->format('Y-m-d H:i:s'),
            ]
        ]);
    }
}
