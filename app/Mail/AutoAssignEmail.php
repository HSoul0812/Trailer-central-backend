<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AutoAssignEmail extends Mailable
{
    use Queueable, SerializesModels;

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
        $this->data     = $data;
        $this->subject  = $this->buildSubject($data);
        $this->body     = $this->buildBody($data);
        $this->callbacks[] = function ($message) use ($data) {
            $message->getHeaders()->addTextHeader('Message-ID', $data['id']);
        };
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

        $build = $this->from($from, $name);

        if (! empty($this->data['replyToEmail'])) {
            $build->replyTo($this->data['replyToEmail'], $this->data['replyToName']);
        }

        $build->view('emails.interactions.interaction-email')
            ->text('emails.interactions.interaction-email-plain');

        if (! empty($this->data['attach']) && is_array($this->data['attach'])) {
            foreach ($this->data['attach'] as $attach) {
                $build->attach($attach['path'], [
                    'as'    => $attach['as'],
                    'mime'  => $attach['mime']
                ]);
            }
        }

        $build->with($this->data);

        return $build;
    }

    /**
     * Build Subject
     * 
     * @param type $data
     */
    public function buildSubject($data) {
        // Initialize
        $subject = 'Assigned to Handle Lead';

        // Lead Name Exists?!
        if(!empty($data['lead_name'])) {
            $subject .= ' "' . $data['lead_name'] . '"';
        }

        // Return Subject
        return $subject;
    }

    /**
     * Build Body for Email
     * 
     * @param type $data
     * @return boolean
     */
    public function buildBody($data) {
        // Clean Up Next Contact Date Output
        $nextContactText = '';
        if(!empty($data['next_contact_date'])) {
            $nextContactDate = new \DateTime($data['next_contact_date'], new \DateTimeZone(env('DB_TIMEZONE')));
            $nextContactText = ' on ' . $nextContactDate->format("l, F jS, Y") .
                                ' at ' . $nextContactDate->format("g:i A T");
        }

        // Initialize Body
        $body = $data['salesperson_name'] . ', you have been assigned to handle the ' .
                    'following lead "' . $data['lead_name'] . '"' . $nextContactText . '.<br />' .
                    'See below for details.<br /><br />';

        // Launch CRM to Exact URL
        if(!empty($data['launch_url'])) {
            $body .= '<a href="' . $data['launch_url'] . '">Click here to open this lead in Trailer Central CRM!</a><br /><br />';
        }

        // Handle Lead Details
        if(!empty($data['lead_email'])) {
            $body .= '<strong>Email Address:</strong> ' . $data['lead_email'] . '<br />';
        }
        if(!empty($data['lead_phone'])) {
            $body .= '<strong>Phone Number:</strong> ' . $data['lead_phone'] . '<br />';
        }
        if(!empty($data['lead_status'])) {
            $body .= '<strong>Status:</strong> ' . $data['lead_status'] . '<br />';
        }
        if(!empty($data['lead_address'])) {
            $body .= '<strong>Address:</strong><br />' . $data['lead_address'] . '<br />';
        }
        if(!empty($data['lead_comments'])) {
            $body .= '<br /><blockquote>' . $data['lead_comments'] . '</blockquote>';
        }

        // Return Body
        return $body;
    }
}
