<?php

namespace App\Transformers\CRM\Interactions\Message;

use App\Models\CRM\Interactions\InteractionMessage;
use League\Fractal\TransformerAbstract;

/**
 * Class SearchCountOfTransformer
 * @package App\Transformers\CRM\Interactions\Message
 */
class SearchCountOfTransformer extends TransformerAbstract
{
    /**
     * @var string
     */
    private $field;

    /**
     * @param string $field
     */
    public function __construct(string $field)
    {
        $this->field = $field;
    }

    /**
     * @param array|\StdClass $data
     * @return array
     */
    public function transform($data): array
    {
        $data = json_decode(json_encode($data), true);

        if ($this->field === 'message_type') {
            foreach (InteractionMessage::MESSAGE_TYPES as $messageType) {
                $data[$messageType] = $data[$messageType] ?? 0;
            }
        }

        return $data;
    }
}
