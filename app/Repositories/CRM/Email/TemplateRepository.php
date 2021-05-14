<?php

namespace App\Repositories\CRM\Email;

use App\Exceptions\NotImplementedException;
use App\Models\CRM\Email\Template;
use App\Repositories\CRM\Email\TemplateRepositoryInterface;

class TemplateRepository implements TemplateRepositoryInterface {

    public function create($params) {
        throw new NotImplementedException;
    }

    public function delete($params) {
        throw new NotImplementedException;
    }

    public function get($params) {
        return Template::findOrFail($params['id']);
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
     * @return string
     */
    public function fillTemplate(string $template, array $replaces): string {
        // Loop Replacements!
        $body = $template;
        foreach($replaces as $field => $value) {
            // Replace Field
            $body = str_replace('{' . $field . '}', $value, $body);
        }

        // Return Result Template Body Email
        return $body;
    }
}
