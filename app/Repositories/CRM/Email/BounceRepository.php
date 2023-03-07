<?php

namespace App\Repositories\CRM\Email;

use App\Exceptions\NotImplementedException;
use App\Models\CRM\Email\Bounce;
use App\Repositories\CRM\Email\BounceRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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

    public function update($params)
    {
        $bounce = Bounce::findOrFail($params['email_bounce_id']);

        DB::transaction(function () use (&$bounce, $params) {
            $bounce->fill($params)->save();
        });

        return $bounce;
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

    /**
     * Get all malformed
     */
    public function getAllMalformed(): Collection
    {
        return Bounce::where('email_address', 'LIKE', '%<%>%')->get();
    }

    /**
     * Try to parse the email
     *
     * If there is not a match for an email, it will
     * return the received string to avoid issues.
     *
     * @param string $email
     * @return string
     */
    public function parseEmail(string $email): string
    {
        $matches = preg_match('([a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.[a-zA-Z0-9_-]+)', $email, $emails);
        return $matches ? $emails[0] : $email;
    }
}
