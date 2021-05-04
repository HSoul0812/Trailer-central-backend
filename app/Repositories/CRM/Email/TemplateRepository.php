<?php

namespace App\Repositories\CRM\Email;

use App\Repositories\CRM\Email\TemplateRepositoryInterface;
use App\Exceptions\NotImplementedException;
use App\Models\CRM\Email\Template;

class TemplateRepository implements TemplateRepositoryInterface {

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
     * Fill Email Template Body
     * 
     * @param string $template
     * @param array $replaces
     * @return type
     */
    public function fillTemplate($template, $replaces) {
        // Add Footer
        $body = $template . Template::REPLY_STOP;

        // Loop Replacements!
        foreach($replaces as $field => $value) {
            // Replace Field
            $body = str_replace('{' . $field . '}', $value, $body);
        }

        // Return Result Template Body Email
        return $body;
    }
}
