<?php

declare(strict_types=1);

namespace App\Services\Common;

use App\Contracts\LoggerServiceInterface;
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
    public function emergency($message, array $context = [])
    {
        Log::emergency($message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function alert($message, array $context = [])
    {
        Log::alert($message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function critical($message, array $context = [])
    {
        Log::critical($message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function error($message, array $context = [])
    {
        Log::error($message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function warning($message, array $context = [])
    {
        Log::warning($message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function notice($message, array $context = [])
    {
        Log::notice($message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function info($message, array $context = [])
    {
        Log::info($message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function debug($message, array $context = [])
    {
        Log::debug($message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function log($level, $message, array $context = [])
    {
        Log::log($level, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function channel(?string $channel)
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
