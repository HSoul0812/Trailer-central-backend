<?php

namespace App\Mail\CRM;

use App\Services\CRM\Interactions\DTOs\BuilderEmail;
use App\Services\Integration\Common\DTOs\ParsedEmail;
use Illuminate\Foundation\Application;
use Illuminate\Mail\Mailer;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\TransportManager;

class CustomEmail extends Mailable
{
    /**
     * @const string
     */
    const BLADE_HTML = 'emails.interactions.interaction-email';
    const BLADE_PLAIN = 'emails.interactions.interaction-email-plain';

    /**
     * @var array
     */
    protected $data;

    /**
     * @var ParsedEmail
     */
    protected $parsedEmail;

    /**
     * Create a new message instance.
     *
     * @param ParsedEmail $email
     * @param null|BuilderEmail $config
     */
    public function __construct(ParsedEmail $email, ?BuilderEmail $config = null)
    {
        $this->parsedEmail = $email;
        $this->data     = ['body' => $email->body];
        $this->subject  = $email->subject;

        // Override Message-ID?
        $this->callbacks[] = function ($message) use (&$messageId, $email, $config) {
            $message->getHeaders()->get('Message-ID')->setId($email->cleanMessageId());
            $message->getHeaders()->addTextHeader('X-SES-CONFIGURATION-SET', config('services.ses.options.ConfigurationSetName'));

            // BuilderEmail Config Provided?
            if(!empty($config)) {
                $message->getHeaders()->addTextHeader('X-Builder-Email-ID', $config->id);
                $message->getHeaders()->addTextHeader('X-Builder-Email-Type', $config->type);
                $message->getHeaders()->addTextHeader('X-Builder-Email-Lead', $config->leadId);
                $message->getHeaders()->addTextHeader('X-Builder-History-ID', $config->emailId);
            }
        };
    }

    /**
     * Build the message.
     *
     * @return CustomEmail
     */
    public function build()
    {
        // Initialize Build
        $build = $this;

        // HTML is NOT Null?
        if(self::BLADE_HTML !== null) {
            $build->view(self::BLADE_HTML);
        }

        // Plain is NOT Null?
        if(self::BLADE_PLAIN !== null) {
            $build->text(self::BLADE_PLAIN);
        }

        // Add Attachments
        if(!empty($this->data['attach'])) {
            $this->applyAttachments($build, $this->data['attach']);
        }

        $build->with($this->data);

        return $build;
    }


    /**
     * Get Custom Mailer
     * 
     * @param Application $app
     * @param array{fromName: string, fromEmail: string, ?password: string,
     *              ?host: string, ?port: int, ?security: string} $config
     * @return Mailer
     */
    public static function getCustomMailer(Application $app, array $config): Mailer
    {
        // Set Defaults
        $host = $config['host'] ?? config('mail.host');
        $port = $config['port'] ?? config('mail.port');
        $fromEmail = $config['fromEmail'] ?? config('mail.from.address');
        $fromName = $config['fromName'] ?? config('mail.from.name');
        $username = $config['fromEmail'] ?? config('mail.username');
        $password = $config['password'] ?? config('mail.password');
        $security = $config['security'] ?? config('mail.encryption');

        // Create Smtp Transport
        $transport = new \Swift_SmtpTransport($host, $port);
        if(!empty($config['authMode'])) {
            $transport->setAuthMode($config['authMode']);
        }
        $transport->setUsername($username);
        $transport->setPassword($password);
        $transport->setEncryption($security);

        // Create Swift Mailer
        $swift_mailer = new \Swift_Mailer($transport);
        $mailer = new Mailer($app->get('view'), $swift_mailer, $app->get('events'));
        $mailer->alwaysFrom($fromEmail, $fromName);
        if(!empty($config['replyEmail'])) {
            $mailer->alwaysReplyTo($config['replyEmail'], $config['fromName']);
        }

        // Return Mailer
        return $mailer;
    }

    /**
     * Get Custom SES Mailer
     * 
     * @param Application $app
     * @param array{fromName: string, replyEmail: string} $config
     * @return Mailer
     */
    public static function getCustomSesMailer(Application $app, array $config = []): Mailer
    {
        // Set Defaults
        $fromEmail = $config['fromEmail'] ?? config('services.ses.from.address');
        $fromName = $config['fromName'] ?? config('services.ses.from.name');

        // Get SES Driver
        $transport = new TransportManager($app);
        $transport->setDefaultDriver('ses');
        $driver = $transport->driver();

        // Create Swift Mailer
        $swift_mailer = new \Swift_Mailer($driver);
        $mailer = new Mailer($app->get('view'), $swift_mailer, $app->get('events'));
        $mailer->alwaysFrom($fromEmail, $fromName);
        if(!empty($config['replyEmail'])) {
            $mailer->alwaysReplyTo($config['replyEmail'], $config['fromName']);
        }

        // Return Mailer
        return $mailer;
    }
}
