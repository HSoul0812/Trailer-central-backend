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
     * Validate Config
     * 
     * @param array $config
     * @return array of set config, or default app config if set config is invalid
     */
    private function validateConfig($config) {
        // If ANYTHING Important is Missing, Fallback to Defaults!
        if(empty($config['smtp_host']) || empty($config['smtp_port']) ||
           empty($config['smtp_username']) || empty($config['smtp_password']) ||
           empty($config['smtp_encryption'])) {
            // Set All Defaults!
            return [
                'smtp_host' => Config::get('mail.host'),
                'smtp_port' => Config::get('mail.post'),
                'smtp_username' => Config::get('mail.username'),
                'smtp_password' => Config::get('mail.password'),
                'smtp_encryption' => Config::get('mail.encryption'),
                'from_email' => Config::get('mail.from.address'),
                'from_name' => Config::get('mail.from.name')
            ];
        }

        // Only Uneeded Things are Empty!
        if(empty($config['from_email'])) {
            $config['from_email'] = $config['smtp_username'];
        }

        // Return Fallback Config
        return $config;
    }

    /**
     * Fix To Config
     * 
     * To Must be Array of Arrays or Just Email!
     * 
     * @param string|array $to
     * @return string|array
     */
    private function fixTo($to) {
        // Is Array?
        if(is_array($to)) {
            // Only Single?
            if(isset($to['email'])) {
                // Validate Name!
                if(isset($to['name']) && empty($to['name'])) {
                    unset($to['name']);
                }

                // Return To As Array!
                return [$to];
            } else {
                // Loop To Array!
                foreach($to as $k => $v) {
                    // Remove if To Email Invalid!
                    if(!isset($v['email'])) {
                        unset($to[$k]);
                    }

                    // Remove Name if Name Empty!
                    if(isset($v['name']) && empty($v['name'])) {
                        unset($v['name']);
                        $to[$k] = $v;
                    }
                }
            }
        }

        // Return To As Normal!
        return $to;
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