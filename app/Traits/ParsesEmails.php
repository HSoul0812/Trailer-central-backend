<?php

namespace App\Traits;

trait ParsesEmails
{
    /** @var string */
    private $validate_regex = '([a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.[a-zA-Z0-9_-]+)';

    /**
     * Validate if it's an email
     *
     * @param string $email
     * @return bool
     */
    private function validateEmail(string $email): bool
    {
        $matches = preg_match($this->validate_regex, $email);
        return boolval($matches);
    }

    /**
     * Parse email if possible, if not, returns the same string.
     *
     * @param string $email
     * @return string
     */
    public function parseEmail(string $email): string
    {
        $matches = preg_match($this->validate_regex, $email, $emails);
        return $matches ? $emails[0] : $email;
    }

    /**
     * Parse array of strings as emails.
     *
     * @param array $emails
     * @return array
     */
    public function parseEmails(array $emails): array
    {
        $parsedEmails = [];

        foreach ($emails as $email) {
            if ($this->validateEmail($email)) {
                $parsedEmails[] = $this->parseEmail($email);
            }
        }

        return $parsedEmails;
    }
}
