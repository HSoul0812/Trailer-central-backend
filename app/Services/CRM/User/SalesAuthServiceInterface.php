<?php

namespace App\Services\CRM\User;

interface SalesAuthServiceInterface {
    /**
     * Show Sales Auth Response
     * 
     * @param array $params
     * @return Fractal
     */
    public function show($params);

    /**
     * Create Sales Auth
     * 
     * @param array $params
     * @return Fractal
     */
    public function create($params);

    /**
     * Update Sales Auth
     * 
     * @param array $params
     * @return Fractal
     */
    public function update($params);
}