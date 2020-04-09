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
    /**
     * @param $files - mail attachment(-s)
     * @return bool | string
     */
    public function checkAttachmentsSize($files)
    {
        $totalSize = 0;
        foreach ($files as $file) {
            if ($file['size'] > 2097152) {
                return "Single upload size must be less than 2 MB.";
            } else if ($totalSize > 8388608) {
                return "Total upload size must be less than 8 MB";
            }
            $totalSize += $file['size'];
        }
        return false;
    }
}
