<?php

namespace App\Transformers\CRM\Interactions;

use App\Repositories\CRM\Text\TextLogFileRepositoryInterface;
use App\Traits\S3\S3Helper;
use League\Fractal\TransformerAbstract;
use App\Models\CRM\Interactions\Interaction;
use App\Transformers\CRM\Leads\LeadTransformer;
use App\Transformers\CRM\User\SalesPersonTransformer;
use Carbon\Carbon;

class InteractionTransformer extends TransformerAbstract
{
    use S3Helper;

    /**
     * @var SalesPersonTransformer
     */
    private $salesPersonTransformer;

    /**
     * @var TextLogFileRepositoryInterface
     */
    private $textLogFileRepository;

    protected $defaultIncludes = [
        'salesPerson',
        'emailHistory'
    ];

    protected $availableIncludes = [
        'lead',
    ];

    /**
     * SalesPersonTransformer constructor.
     * @param SalesPersonTransformer $salesPersonTransformer
     * @param EmailHistoryTransformer $emailHistoryTransformer
     */
    public function __construct(
        SalesPersonTransformer $salesPersonTransformer,
        EmailHistoryTransformer $emailHistoryTransformer,
        TextLogFileRepositoryInterface $textLogFileRepository
    ) {
        $this->salesPersonTransformer = $salesPersonTransformer;
        $this->emailHistoryTransformer = $emailHistoryTransformer;
        $this->textLogFileRepository = $textLogFileRepository;
    }

    /**
     * Transform Interaction
     *
     * @param Interaction $interaction
     * @return array
     */
    public function transform(Interaction $interaction): array
    {
        $files = [];

        if ($interaction->interaction_type === Interaction::TYPE_EMAIL) {
            foreach ($interaction->emailHistory as $emailHistory) {
                foreach ($emailHistory->attachments as $attachment) {
                    $files[] = $attachment->filename;
                }
            }
        } elseif ($interaction->interaction_type === Interaction::TYPE_TEXT) {
            /*
            Since dealer_texts_log table is not related to crm_interaction table, a query with union is used for getting text interactions.
            Therefore, an additional query has been added here to get files related to dealer_texts_log
            */
            $textLogFiles = $this->textLogFileRepository->getAll(['dealer_texts_log_id' => $interaction->interaction_id]);

            foreach ($textLogFiles as $file) {
                $files[] = $this->getS3Url($file->path);
            }
        }

        return [
            'id' => $interaction->interaction_id,
            'user_id' => $interaction->user_id,
            'type' => $interaction->interaction_type,
            'time' => Carbon::parse($interaction->interaction_time)->format('F d, Y g:i A'),
            'notes' => $interaction->interaction_notes,
            'contact_name' => ($interaction->lead) ? $interaction->lead->full_name : '',
            'username' => $interaction->real_username,
            'to_no' => $interaction->to_no,
            'interaction_time' => $interaction->interaction_time,
            'files' => $files
        ];
    }

    public function includeLead(Interaction $interaction)
    {
        return $this->item($interaction->lead, new LeadTransformer());
    }

    public function includeSalesPerson(Interaction $interaction)
    {
        if ($interaction->leadStatus && $interaction->leadStatus->salesPerson) {
            return $this->item($interaction->leadStatus->salesPerson, $this->salesPersonTransformer);
        } else {
            return $this->null();
        }
    }

    public function includeEmailHistory(Interaction $interaction)
    {
        return $this->collection($interaction->emailHistory, $this->emailHistoryTransformer);
    }
}
