<?php

namespace App\Mail\CRM;

use App;
use App\Services\CRM\Email\DTOs\SmtpConfig;
use App\Services\Integration\Common\DTOs\ParsedEmail;
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
     * @var array
     */
    protected $data;

    /**
     * Create a new message instance.
     *
     * @param ParsedEmail $email
     */
    public function __construct(ParsedEmail $email)
    {
        $this->data     = ['body' => $email->body];
        $this->subject  = $email->subject;

        // Override Message-ID?
        if(!empty($email->messageId)) {
            $this->callbacks[] = function ($message) use ($email) {
                $message->getHeaders()->get('Message-ID')->setId($email->messageId);
            };
        }
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $from = config('mail.from.address', 'noreply@trailercentral.com');
        $name = config('mail.from.name', 'Trailer Central');
        if(!empty($this->data['from_name'])) {
            $name = $this->data['from_name'];
        }

        $build = $this->from($from, $name);

        if (! empty($this->data['replyToEmail'])) {
            $build->replyTo($this->data['replyToEmail'], $this->data['replyToName']);
        }

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
     * @param App $app
     * @param SmtpConfig $config
     * @return Mailer
     */
    public static function getCustomMailer(App $app, SmtpConfig $config): Mailer
    {
        // Create Smtp Transport
        $transport = new \Swift_SmtpTransport($config->getHost(), $config->getPort());
        $transport->setUsername($config->getUsername());
        $transport->setPassword($config->getPassword());
        $transport->setEncryption($config->getSecurity());

        // Create Swift Mailer
        $swift_mailer = new \Swift_Mailer($transport);
        $mailer = new Mailer($app->get('view'), $swift_mailer, $app->get('events'));
        $mailer->alwaysFrom($config->getFromEmail(), $config->getUsername());
        $mailer->alwaysReplyTo($config->getFromEmail(), $config->getUserName());

        // Return Mailer
        return $mailer;
    }
}
