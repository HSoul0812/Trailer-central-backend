<?php

namespace App\Traits;

use App\Models\User\User;
use Illuminate\Support\Facades\Config;

trait MailHelper
{
    /**
     * @param User $user
     */
    public function setSalesPersonSmtpConfig(User $user): void
    {
        if (! empty($user->salesPerson) && ! empty($user->salesPerson->smtp_server)) {
            $config = [
                'driver'        => 'smtp',
                'host'          => $user->salesPerson->smtp_server,
                'port'          => $user->salesPerson->smtp_port ?? '2525',
                'username'      => $user->salesPerson->smtp_email,
                'password'      => $user->salesPerson->smtp_password,
                'encryption'    => $user->salesPerson->smtp_security ?? 'tls'
            ];
            Config::set('mail', $config);
        }
    }
}
