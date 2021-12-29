<?php

namespace App\Services\Integration\Facebook;

interface ChatServiceInterface {
    /**
     * Show Chat Response
     * 
     * @param array $params
     * @return array<ChatTransformer>
     */
    public function show(array $params): array;

    /**
     * Create Chat
     * 
     * @param array $params
     * @return array<ChatTransformer>
     */
    public function create(array $params): array;

    /**
     * Update Chat
     * 
     * @param array $params
     * @return array<ChatTransformer>
     */
    public function update(array $params): array;

    /**
     * Delete Chat
     * 
     * @param int $id
     * @return boolean
     */
    public function delete(int $id): bool;
}