<?php

namespace App\Jobs\Mailer;

use Illuminate\Mail\Mailer;
use Illuminate\Support\Facades\Config;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UserMailerJob implements ShouldQueue {

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $configuration;
    public $to;
    public $mailable;

    /**
     * Create a new job instance.
     *
     * @param array $configuration
     * @param string|array $to
     * @param Mailable $mailable
     */
    public function __construct($configuration, $to, $mailable)
    {
        $this->configuration = $this->validateConfig($configuration);
        $this->to = $this->fixTo($to);
        $this->mailable = $mailable;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $mailer = $this->getUserMailer($this->configuration);
        $mailer->to($this->to)->send($this->mailable);
    }


    /**
     * Initialize User Mailer to Bind
     * 
     * @param type $app
     * @param type $params
     * @return Mailer
     */
    private function getUserMailer($params) {
        // Get SMTP Details
        $smtp_host = $params['smtp_host'];
        $smtp_port = $params['smtp_port'];
        $smtp_username = $params['smtp_username'];
        $smtp_password = $params['smtp_password'];
        $smtp_encryption = $params['smtp_encryption'];

        // Get From Details
        $from_email = $params['from_email'];
        $from_name = $params['from_name'];

        // Create Swift SMTP Transport
        $transport = new \Swift_SmtpTransport($smtp_host, $smtp_port);
        $transport->setUsername($smtp_username);
        $transport->setPassword($smtp_password);
        $transport->setEncryption($smtp_encryption);

        // Create Swift Mailer
        $swift_mailer = new \Swift_Mailer($transport);

        // Create Mailer
        $mailer = new Mailer(app()->get('view'), $swift_mailer, app()->get('events'));
        $mailer->alwaysFrom($from_email, $from_name);
        $mailer->alwaysReplyTo($from_email, $from_name);

        // Return Mailer!
        return $mailer;
    }
}