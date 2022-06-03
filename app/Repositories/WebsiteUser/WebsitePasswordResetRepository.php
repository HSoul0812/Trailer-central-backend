<?php

namespace App\Repositories\WebsiteUser;

use App\Models\WebsitePasswordReset;

class WebsitePasswordResetRepository implements WebsitePasswordResetRepositoryInterface
{

    public function create(array $attributes): WebsitePasswordReset
    {
        $passwordReset = new WebsitePasswordReset($attributes);
        $passwordReset->save();
        return $passwordReset;
    }
}
