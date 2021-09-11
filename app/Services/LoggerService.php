<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Psr\Log\LoggerInterface;

/**
 * Provides a logger instance according to PSR-3.
 */
class LoggerService implements LoggerServiceInterface
{
    /**
     * {@inheritdoc}
     */
    public function emergency($message, array $context = []): void
    {
        Log::emergency($message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function alert($message, array $context = []): void
    {
        Log::alert($message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function critical($message, array $context = []): void
    {
        Log::critical($message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function error($message, array $context = []): void
    {
        Log::error($message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function warning($message, array $context = []): void
    {
        Log::warning($message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function notice($message, array $context = []): void
    {
        Log::notice($message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function info($message, array $context = []): void
    {
        Log::info($message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function debug($message, array $context = []): void
    {
        Log::debug($message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function log($level, $message, array $context = []): void
    {
        Log::log($level, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function channel(?string $channel): LoggerInterface
    {
        return Log::channel($channel);
    }

    /**
     * {@inheritdoc}
     */
    public function stack(array $channels, ?string $channel): LoggerInterface
    {
        return Log::stack($channels, $channel);
    }
}
