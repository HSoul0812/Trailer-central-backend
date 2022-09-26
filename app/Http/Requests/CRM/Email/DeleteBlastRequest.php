<?php

namespace App\Http\Requests\CRM\Email;

use App\Http\Requests\Request;

/**
 * Delete Blast Request
 *
 * @author David A Conway Jr.
 */
class DeleteBlastRequest extends Request
{
    protected $rules = [
        'id' => 'integer'
    ];
}
