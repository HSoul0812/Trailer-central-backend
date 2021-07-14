<?php

namespace App\Mail\CRM;

use App\Services\Integration\Common\DTOs\ParsedEmail;
use Illuminate\Foundation\Application;
use Illuminate\Mail\Mailer;
use Illuminate\Mail\Mailable;

class CustomEmail extends Mailable
{
    /**
     * @const string
     */
    const BLADE_HTML = 'emails.interactions.interaction-email';
    const BLADE_PLAIN = 'emails.interactions.interaction-email-plain';

    /**
     * @var string
     */
    public $messageId;

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
     */
    public function __construct(ParsedEmail $email)
    {
        $this->parsedEmail = $email;
        $this->data     = ['body' => $email->body];
        $this->subject  = $email->subject;

        // Override Message-ID?
        $messageId = '';
        $this->callbacks[] = function ($message) use (&$messageId, $email) {
            $message->getHeaders()->get('Message-ID')->setId($email->cleanMessageId());
            $message->getHeaders()->addTextHeader('X-SES-MESSAGE-TAGS', 'emailHistoryId=' . $email->emailHistoryId);

            // SES Message ID Exists?!
            $sesMessageId = $message->getHeaders()->get('X-SES-Message-ID');
            if(!empty($sesMessageId)) {
                $messageId = $sesMessageId->getValue();
            }
        };

        // Return CustomEmail With Up-To-Date Message Id
        $this->messageId = $messageId;
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
}
