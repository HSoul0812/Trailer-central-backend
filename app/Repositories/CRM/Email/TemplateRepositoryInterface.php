<?php

namespace App\Repositories\CRM\Email;

use App\Repositories\Repository;

interface TemplateRepositoryInterface extends Repository {
    /**
     * Fill Email Template Body
     * 
     * @param string $template
     * @param array $replaces
     * @return type
     */
    public function fillTemplate($template, $replaces);
}
