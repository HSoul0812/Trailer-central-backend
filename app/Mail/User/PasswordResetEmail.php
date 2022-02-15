<?php

namespace App\Mail\User;

use App\Traits\WithGetter;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetEmail extends Mailable
{
    const SUBJECT = "Password Reset Request";

    use SerializesModels;
    use WithGetter;

    /**
     * @var array
     */
    private $data;

    /**
     * Create a new message instance.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
        $this->data['resetUrl'] = config('password-reset.email.endpoint');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $build = $this->from(config('mail.from.address'), config('mail.from.name'));
        $build->subject(self::SUBJECT);

        $build->view('emails.user.password-reset');

        $build->with($this->data);

        return $build;
    }
}
