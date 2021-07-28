<?php

namespace App\Repositories\CRM\User;

use App\Models\CRM\User\TimeClock;
use App\Repositories\RepositoryAbstract;

class TimeClockRepository extends RepositoryAbstract implements TimeClockRepositoryInterface
{
    /**
     * Checks if the user/employee has the clock ticking currently.
     *
     * @param int $userId
     * @return bool
     */
    public function isClockTicking(int $userId): bool
    {
        $timeClock = TimeClock::where('user_id', $userId)
            ->whereNull('punch_out')
            ->first();

        return $timeClock !== null;
    }

    /**
     * Starts the clock for given user/employee
     *
     * @param int $userId
     * @return bool
     * @throws \Exception
     */
    public function markPunchIn(int $userId): bool
    {
        $timeClock = TimeClock::where('user_id', $userId)
            ->whereNotNull('punch_in')
            ->whereNull('punch_out')
            ->first();
        if ($timeClock) {
            throw new \Exception('Time clock is already ticking');
        }

        $clock = TimeClock::create([
            'user_id' => $userId,
            'punch_in' => date('Y-m-d H:i:s'),
            'punch_out' => null,
        ]);

        return (bool)$clock;
    }

    /**
     * Starts the clock for given user/employee
     *
     * @param int $userId
     * @return bool
     * @throws \Exception
     */
    public function markPunchOut(int $userId): bool
    {
        $timeClock = TimeClock::where('user_id', $userId)
            ->whereNotNull('punch_in')
            ->whereNull('punch_out')
            ->first();
        if ($timeClock === null) {
            throw new \Exception('Time clock is not ticking!');
        }

        $timeClock->punch_out = date('Y-m-d H:i:s');

        return $timeClock->save();
    }
}
