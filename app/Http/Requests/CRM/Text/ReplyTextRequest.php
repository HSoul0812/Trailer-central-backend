<?php

namespace App\Http\Requests\CRM\Text;

use App\Http\Requests\Request;

/**
 * Class ReplyTextRequest
 * @package App\Http\Requests\CRM\Text
 */
class ReplyTextRequest extends Request
{
    /**
     * @return array
     */
    public function getRules(): array
    {
        return [
            'Body' => 'required|string',
            'From' => 'required|string',
            'To' => 'required|string|active_interaction:' . $this->input('From'),
            'MediaUrl0' => 'nullable|string|url',
            'MediaUrl1' => 'nullable|string|url',
            'MediaUrl2' => 'nullable|string|url',
            'MediaUrl3' => 'nullable|string|url',
            'MediaUrl4' => 'nullable|string|url',
            'MediaUrl5' => 'nullable|string|url',
            'MediaUrl6' => 'nullable|string|url',
            'MediaUrl7' => 'nullable|string|url',
            'MediaUrl8' => 'nullable|string|url',
            'MediaUrl9' => 'nullable|string|url',
        ];
    }
}
