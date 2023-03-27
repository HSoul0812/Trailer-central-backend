<?php

namespace App\Repositories\User;

use App\Exceptions\User\TooLongPasswordException;
use App\Exceptions\User\WrongCurrentPasswordException;
use App\Services\Common\EncrypterServiceInterface;
use App\Models\User\DealerUser;
use App\Models\User\DealerPasswordReset;
use App\Exceptions\NotImplementedException;
use App\Mail\User\PasswordResetEmail;
use App\Models\User\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DealerPasswordResetRepository implements DealerPasswordResetRepositoryInterface {

    /**
     * @var EncrypterServiceInterface
     */
    private $encrypterService;

    /**
     * @param  EncrypterServiceInterface  $encrypterService
     */
    public function __construct(EncrypterServiceInterface $encrypterService)
    {
        $this->encrypterService = $encrypterService;
    }

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

        $this->updateDealerPassword($dealer, $password);

        $dealerPasswordReset->status = DealerPasswordReset::STATUS_PASSWORD_RESET_COMPLETED;

        return $dealerPasswordReset->save();
    }

    /**
     * {@inheritDoc}
     */
    public function getByCode(string $code) : DealerPasswordReset
    {
        return DealerPasswordReset::where('code', $code)->where('status', DealerPasswordReset::STATUS_PASSWORD_RESET_INITIATED)->firstOrFail();
    }

    /**
     * {@inheritDoc}
     * @throws WrongCurrentPasswordException when current password is wrong
     * @throws TooLongPasswordException when the password is greater than eighth characters
     */
    public function updateDealerPassword(User $dealer, string $password, string $current_password) : void
    {
        $this->passwordMatch($dealer->password, $current_password, $dealer->salt);

        if (empty($dealer->salt)) {
            $salt = uniqid();
            DB::statement("UPDATE dealer SET salt = '{$salt}' WHERE dealer_id = {$dealer->dealer_id}");
        }

        DB::statement("UPDATE dealer SET password = ENCRYPT('{$password}', salt) WHERE dealer_id = {$dealer->dealer_id}");
    }

    /**
     * {@inheritDoc}
     * @throws WrongCurrentPasswordException when current password is wrong
     * @throws TooLongPasswordException when the password is greater than eighth characters
     */
    public function updateDealerUserPassword(DealerUser $user, string $password, string $current_password) : void
    {
        $this->passwordMatch($user->password, $current_password, $user->salt);

        DB::statement("UPDATE dealer_users SET password = ENCRYPT('{$password}', salt) WHERE dealer_user_id = {$user->dealer_user_id}");
    }

    /**
     * @param string $expectedPassword
     * @param string $password
     * @return void
     * @throws WrongCurrentPasswordException when the current password is wrong
     */
    private function passwordMatch(string $expectedPassword, string $password, string $salt)
    {
        if($expectedPassword !== $this->encrypterService->encryptBySalt($password, $salt)) {
            throw new WrongCurrentPasswordException();
        }
    }
}
