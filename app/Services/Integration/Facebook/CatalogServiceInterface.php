<?php

namespace App\Services\Integration\Facebook;

interface CatalogServiceInterface {
    /**
     * Show Catalog Response
     * 
     * @param array $params
     * @return Fractal
     */
    public function show($params);

    /**
     * Create Catalog
     * 
     * @param array $params
     * @return Fractal
     */
    public function create($params);

    /**
     * Update Catalog
     * 
     * @param array $params
     * @return Fractal
     */
    public function update($params);
}