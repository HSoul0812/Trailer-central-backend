<?php

namespace App\Http\Requests\CRM\User;

use App\Http\Requests\Request;
use Illuminate\Validation\Rule;

class UpdateSettingsRequest extends Request {

    protected $rules = [
        'user_id' => 'required|integer',

        'price_per_mile' => 'float',
        'email_signature' => 'string',
        'timezone' => 'string|valid_timezone',
        'enable_hot_potato' => 'boolean',
        'disable_daily_digest' => 'boolean',
        'enable_assign_notification' => 'boolean',
        'enable_due_notification' => 'boolean',
        'enable_past_notification' => 'boolean',

        'default/filters/sort' => 'integer|valid_sort_orders_crm',
        'round-robin/hot-potato/delay' => 'integer',
        'round-robin/hot-potato/duration' => 'integer',
        'round-robin/hot-potato/end-hour' => 'string',
        'round-robin/hot-potato/skip-weekends' => 'boolean',
        'round-robin/hot-potato/start-hour' => 'string',
        'round-robin/hot-potato/use-submission-date' => 'boolean'
    ];
}
