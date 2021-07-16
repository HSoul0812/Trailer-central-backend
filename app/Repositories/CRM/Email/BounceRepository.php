<?php

namespace App\Repositories\CRM\Email;

use App\Exceptions\NotImplementedException;
use App\Models\CRM\Email\Bounce;
use App\Repositories\CRM\Email\BounceRepositoryInterface;

class BounceRepository implements BounceRepositoryInterface {

    public function create($params) {
        throw new NotImplementedException;
    }

    public function delete($params) {
        throw new NotImplementedException;
    }

    public function get($params) {
        return Blast::findOrFail($params['id']);
    }

    public function getAll($params) {
        throw new NotImplementedException;
    }

    public function update($params) {
        throw new NotImplementedException;
    }

    /**
     * Was Email Address Bounced/Complained/Unsubscribed?
     * 
     * @param string $email
     * @return null|string
     */
    public function wasBounced(string $email): ?string {
        // Get Email Bounced Entry
        $bounced = Bounce::where('email_address', $email)->first();

        // If Email is Marked as Bounced, Return Type of Bounce!
        return $bounced->type ?? null;
    }
}
