<?php

namespace App\Repositories\User;

use App\Repositories\User\DealerPasswordResetRepositoryInterface;
use App\Models\User\DealerPasswordReset;
use App\Exceptions\NotImplementedException;
use App\Mail\User\PasswordResetEmail;
use App\Models\User\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DealerPasswordResetRepository implements DealerPasswordResetRepositoryInterface {
    
    public function create($params) {
        throw new NotImplementedException;
    }

    public function delete($params) {
        throw new NotImplementedException;
    }

    public function get($params) {
        throw new NotImplementedException;
    }

    public function getAll($params) {
        throw new NotImplementedException;
    }

    public function update($params) {
        throw new NotImplementedException;
    }
    
    /**
     * {@inheritDoc}
     */
    public function initiatePasswordReset(User $dealer) : DealerPasswordReset
    {
        $dealerPasswordReset = DealerPasswordReset::create([
            'code' => uniqid(),
            'dealer_id' => $dealer->dealer_id,
            'created_at' => Carbon::now(),
            'status' => DealerPasswordReset::STATUS_PASSWORD_RESET_INITIATED
        ]);
        
        Mail::to($dealer->email)->send(
            new PasswordResetEmail([
                'code' => $dealerPasswordReset->code,
            ])
        );
        
        return $dealerPasswordReset;
    }
    
    /**
     * {@inheritDoc}
     */
    public function completePasswordReset(string $code, string $password) : bool
    {
        $dealerPasswordReset = $this->getByCode($code);
        $dealer = $dealerPasswordReset->dealer;
        
        DB::statement("UPDATE dealer SET password = ENCRYPT('{$password}', salt) WHERE dealer_id = {$dealer->dealer_id}");        
        
        $dealerPasswordReset->status = DealerPasswordReset::STATUS_PASSWORD_RESET_COMPLETED;
        
        return $dealerPasswordReset->save();
    }
    
    public function getByCode(string $code) : DealerPasswordReset
    {
        return DealerPasswordReset::where('code', $code)->where('status', DealerPasswordReset::STATUS_PASSWORD_RESET_INITIATED)->firstOrFail();
    }
    
}
